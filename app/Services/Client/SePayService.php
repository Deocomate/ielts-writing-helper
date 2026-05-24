<?php

namespace App\Services\Client;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use SePay\Builders\CheckoutBuilder;
use SePay\SePayClient;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SePayService
{
    private ?SePayClient $client = null;

    public function __construct(private readonly CheckoutService $checkoutService) {}

    /**
     * Create an internal pending transaction and return a signed SePay checkout form.
     *
     * @return array{transaction: Transaction, actionUrl: string, formFields: array<string, mixed>}
     */
    public function createTransactionAndForm(User $user, int $planId): array
    {
        $plan = $this->checkoutService->getPlan($planId);
        $transaction = $this->checkoutService->createPendingTransaction($user, $plan, 'sepay', 'SP');

        return array_merge(
            ['transaction' => $transaction],
            $this->buildCheckoutForm($transaction, $plan),
        );
    }

    /**
     * Build signed checkout fields from the SePay SDK.
     *
     * @return array{actionUrl: string, formFields: array<string, mixed>}
     */
    public function buildCheckoutForm(Transaction $transaction, Plan $plan): array
    {
        $description = Str::limit(
            Str::ascii('Thanh toan goi '.$plan->name.' cho user '.$transaction->user_id),
            250,
            '',
        );

        $checkoutData = CheckoutBuilder::make()
            ->currency('VND')
            ->orderAmount($this->amountToVndInt($transaction->amount))
            ->operation('PURCHASE')
            ->orderDescription($description)
            ->orderInvoiceNumber((string) $transaction->transaction_code)
            ->customerId((string) $transaction->user_id)
            ->successUrl(route('client.checkout.success', $transaction->id))
            ->errorUrl(route('client.checkout.failed', $transaction->id))
            ->cancelUrl(route('client.checkout.failed', $transaction->id))
            ->build();

        return [
            'actionUrl' => $this->client()->checkout()->getCheckoutUrl($this->environment()),
            'formFields' => $this->client()->checkout()->generateFormFields($checkoutData),
        ];
    }

    public function handleIpn(array $payload, string $headerSecretKey): ?Transaction
    {
        $configuredSecret = (string) config('services.sepay.secret_key');

        if ($configuredSecret === '' || ! hash_equals($configuredSecret, $headerSecretKey)) {
            Log::warning('sepay.ipn.invalid_secret', ['ip' => request()->ip()]);

            throw new HttpException(401, 'Unauthorized');
        }

        $invoiceNumber = $this->invoiceNumber($payload);

        if ($invoiceNumber === null) {
            Log::warning('sepay.ipn.missing_invoice', ['payload' => $payload]);

            return null;
        }

        $transaction = Transaction::with(['user', 'plan'])
            ->where('transaction_code', $invoiceNumber)
            ->first();

        if ($transaction === null) {
            Log::warning('sepay.ipn.transaction_not_found', ['invoice' => $invoiceNumber]);

            return null;
        }

        if ($transaction->status === 'success') {
            return $transaction->fresh(['user', 'plan']);
        }

        if ($this->isFailedIpn($payload)) {
            return $this->checkoutService->failTransaction($transaction, [
                'source' => 'sepay_ipn',
                'raw' => $payload,
            ]);
        }

        if (! $this->isSuccessfulIpn($payload)) {
            return $transaction;
        }

        if (! $this->currencyIsValid($payload)) {
            Log::error('sepay.ipn.currency_mismatch', [
                'invoice' => $invoiceNumber,
                'payload' => $payload,
            ]);

            return $transaction;
        }

        if (! $this->amountMatches($transaction, $payload)) {
            Log::error('sepay.ipn.amount_mismatch', [
                'invoice' => $invoiceNumber,
                'expected' => $transaction->amount,
                'received' => $payload['order']['order_amount'] ?? $payload['transaction']['transaction_amount'] ?? null,
            ]);

            return $transaction;
        }

        return $this->checkoutService->confirmTransaction($transaction, [
            'source' => 'sepay_ipn',
            'raw' => $payload,
        ]);
    }

    private function client(): SePayClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        $merchantId = (string) config('services.sepay.merchant_id');
        $secretKey = (string) config('services.sepay.secret_key');

        if ($merchantId === '' || $secretKey === '') {
            throw new RuntimeException('SePay credentials have not been configured.');
        }

        $this->client = new SePayClient(
            $merchantId,
            $secretKey,
            $this->environment(),
            ['timeout' => 60],
        );

        return $this->client;
    }

    private function environment(): string
    {
        return config('services.sepay.environment') === 'production'
            ? SePayClient::ENVIRONMENT_PRODUCTION
            : SePayClient::ENVIRONMENT_SANDBOX;
    }

    private function invoiceNumber(array $payload): ?string
    {
        $invoiceNumber = $payload['order']['order_invoice_number'] ?? null;

        if (! is_string($invoiceNumber) || $invoiceNumber === '') {
            return null;
        }

        return $invoiceNumber;
    }

    private function isSuccessfulIpn(array $payload): bool
    {
        if (($payload['notification_type'] ?? '') !== 'ORDER_PAID') {
            return false;
        }

        $orderStatus = (string) ($payload['order']['order_status'] ?? '');
        $transactionStatus = (string) ($payload['transaction']['transaction_status'] ?? '');

        if ($orderStatus !== '' && $orderStatus !== 'CAPTURED') {
            return false;
        }

        if ($transactionStatus !== '' && $transactionStatus !== 'APPROVED') {
            return false;
        }

        return true;
    }

    private function isFailedIpn(array $payload): bool
    {
        return ($payload['notification_type'] ?? '') === 'TRANSACTION_VOID'
            || ($payload['order']['order_status'] ?? '') === 'CANCELLED'
            || ($payload['transaction']['transaction_status'] ?? '') === 'DECLINED';
    }

    private function currencyIsValid(array $payload): bool
    {
        foreach (['order_currency', 'transaction_currency'] as $field) {
            $source = $field === 'order_currency' ? 'order' : 'transaction';
            $currency = (string) ($payload[$source][$field] ?? '');

            if ($currency !== '' && $currency !== 'VND') {
                return false;
            }
        }

        return true;
    }

    private function amountMatches(Transaction $transaction, array $payload): bool
    {
        $receivedAmount = $payload['order']['order_amount'] ?? $payload['transaction']['transaction_amount'] ?? null;

        if ($receivedAmount === null || $receivedAmount === '') {
            return false;
        }

        return $this->amountToVndInt($transaction->amount) === $this->amountToVndInt($receivedAmount);
    }

    private function amountToVndInt(mixed $amount): int
    {
        return (int) round((float) str_replace(',', '', (string) $amount));
    }
}
