<?php

namespace App\Services\Client;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PayOS\Exceptions\APIException;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\Models\V2\PaymentRequests\PaymentLinkItem;
use PayOS\PayOS;
use RuntimeException;
use Throwable;

class CheckoutService
{
    private ?PayOS $payOSClient = null;

    /**
     * Get an active plan by ID.
     */
    public function getPlan(int $planId): Plan
    {
        return Plan::where('is_active', true)->findOrFail($planId);
    }

    /**
     * Get all active plans for checkout page.
     */
    public function getActivePlans()
    {
        return Plan::where('is_active', true)->orderBy('price')->get();
    }

    /**
     * Create a pending transaction for the user.
     */
    public function createTransaction(User $user, int $planId, string $paymentMethod): Transaction
    {
        $plan = $this->getPlan($planId);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'amount' => $plan->price,
            'payment_method' => $paymentMethod,
            'transaction_code' => 'TXN-'.strtoupper(Str::random(12)),
            'status' => 'pending',
        ]);

        $orderCode = $this->buildOrderCode($transaction->id);
        $amount = (int) round((float) $plan->price);

        $request = new CreatePaymentLinkRequest(
            orderCode: $orderCode,
            amount: $amount,
            description: Str::limit('IELTS PRO #'.$transaction->id, 25, ''),
            cancelUrl: route((string) config('services.payos.cancel_route', 'client.checkout.failed'), ['transaction' => $transaction->id]),
            returnUrl: route((string) config('services.payos.return_route', 'client.checkout.pending'), ['transaction' => $transaction->id]),
            items: [
                new PaymentLinkItem(
                    name: Str::limit($plan->name, 25, ''),
                    quantity: 1,
                    price: $amount,
                ),
            ],
            buyerName: $user->name,
            buyerEmail: $user->email,
            expiredAt: now()->addMinutes(30)->timestamp,
        );

        $paymentLink = $this->payOSClient()->paymentRequests->create($request);

        $transaction->update([
            'gateway_order_code' => $orderCode,
            'checkout_url' => $paymentLink->checkoutUrl,
            'qr_code' => $paymentLink->qrCode,
            'gateway_payload' => [
                'payment_link_id' => $paymentLink->paymentLinkId,
                'status' => $paymentLink->status->value,
                'bin' => $paymentLink->bin,
                'account_number' => $paymentLink->accountNumber,
                'account_name' => $paymentLink->accountName,
                'currency' => $paymentLink->currency,
            ],
        ]);

        return $transaction->fresh(['plan', 'user']);
    }

    /**
     * Confirm a transaction and activate the user's subscription.
     */
    public function confirmTransaction(Transaction $transaction, ?array $gatewayPayload = null): Transaction
    {
        if ($transaction->status === 'success') {
            return $transaction->fresh(['user', 'plan']);
        }

        $transaction->update([
            'status' => 'success',
            'paid_at' => now(),
            'gateway_payload' => $gatewayPayload ?? $transaction->gateway_payload,
        ]);

        $plan = $transaction->plan;
        $user = $transaction->user;

        if ($plan !== null && $user !== null) {
            $expiresAt = $user->subscription_expires_at && $user->subscription_expires_at->isFuture()
                ? $user->subscription_expires_at->copy()->addDays($plan->duration_days)
                : now()->addDays($plan->duration_days);

            $user->update([
                'subscription_tier' => 'pro',
                'subscription_expires_at' => $expiresAt,
            ]);
        }

        return $transaction->fresh();
    }

    /**
     * Mark a transaction as failed.
     */
    public function failTransaction(Transaction $transaction, ?array $gatewayPayload = null): Transaction
    {
        if ($transaction->status === 'success') {
            return $transaction->fresh();
        }

        $transaction->update([
            'status' => 'failed',
            'gateway_payload' => $gatewayPayload ?? $transaction->gateway_payload,
        ]);

        return $transaction->fresh();
    }

    public function refreshPaymentStatus(Transaction $transaction): Transaction
    {
        if ($transaction->status !== 'pending' || empty($transaction->gateway_order_code)) {
            return $transaction->fresh(['plan', 'user']);
        }

        try {
            $paymentLink = $this->payOSClient()->paymentRequests->get((int) $transaction->gateway_order_code);
            $paymentStatus = $paymentLink->status->value;

            if ($paymentStatus === 'PAID') {
                return $this->confirmTransaction($transaction, [
                    'status' => $paymentStatus,
                    'source' => 'payos_get_payment',
                ]);
            }

            if (in_array($paymentStatus, ['CANCELLED', 'FAILED', 'EXPIRED'], true)) {
                return $this->failTransaction($transaction, [
                    'status' => $paymentStatus,
                    'source' => 'payos_get_payment',
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return $transaction->fresh(['plan', 'user']);
    }

    public function handleWebhook(array $payload): ?Transaction
    {
        $webhookData = $this->payOSClient()->webhooks->verify($payload);

        $transaction = Transaction::with(['user', 'plan'])
            ->where('gateway_order_code', $webhookData->orderCode)
            ->first();

        if ($transaction === null) {
            Log::warning('payos.webhook.transaction_not_found', ['order_code' => $webhookData->orderCode]);

            return null;
        }

        $gatewayPayload = [
            'status' => $webhookData->code,
            'description' => $webhookData->desc,
            'reference' => $webhookData->reference,
            'transaction_datetime' => $webhookData->transactionDateTime,
            'payment_link_id' => $webhookData->paymentLinkId,
            'raw' => $payload,
        ];

        if ($webhookData->code === '00') {
            return $this->confirmTransaction($transaction, $gatewayPayload);
        }

        return $this->failTransaction($transaction, $gatewayPayload);
    }

    /**
     * @throws APIException
     */
    private function payOSClient(): PayOS
    {
        if ($this->payOSClient !== null) {
            return $this->payOSClient;
        }

        $clientId = (string) config('services.payos.client_id');
        $apiKey = (string) config('services.payos.api_key');
        $checksumKey = (string) config('services.payos.checksum_key');

        if ($clientId === '' || $apiKey === '' || $checksumKey === '') {
            throw new RuntimeException('PayOS credentials chưa được cấu hình đầy đủ trong .env.');
        }

        $this->payOSClient = new PayOS(
            clientId: $clientId,
            apiKey: $apiKey,
            checksumKey: $checksumKey,
        );

        return $this->payOSClient;
    }

    private function buildOrderCode(int $transactionId): int
    {
        $timestamp = now()->timestamp;

        return (int) ($timestamp.str_pad((string) $transactionId, 4, '0', STR_PAD_LEFT));
    }
}
