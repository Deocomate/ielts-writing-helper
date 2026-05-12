<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PayOS\Exceptions\WebhookException;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function index(Request $request): View
    {
        $planId = $request->query('plan');
        $plan = $planId ? $this->checkoutService->getPlan($planId) : null;

        return view('client.checkout.index', [
            'plan' => $plan,
            'plans' => $this->checkoutService->getActivePlans(),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'payment_method' => ['required', 'string', 'in:payos'],
        ]);

        try {
            $transaction = $this->checkoutService->createTransaction(
                auth()->user(),
                (int) $data['plan_id'],
                $data['payment_method'],
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'payment_method' => 'Không thể tạo giao dịch PayOS lúc này. Vui lòng kiểm tra cấu hình hoặc thử lại sau.',
            ]);
        }

        return redirect()->route('client.checkout.pending', $transaction->id);
    }

    public function success(int $transaction): View
    {
        $txn = auth()->user()->transactions()->with('plan')->findOrFail($transaction);

        return view('client.checkout.success', ['transaction' => $txn]);
    }

    public function failed(int $transaction): View
    {
        $txn = auth()->user()->transactions()->with('plan')->findOrFail($transaction);

        return view('client.checkout.failed', ['transaction' => $txn]);
    }

    public function pending(int $transaction): View|RedirectResponse
    {
        $txn = auth()->user()->transactions()->with('plan')->findOrFail($transaction);
        $txn = $this->checkoutService->refreshPaymentStatus($txn);

        if ($txn->status === 'success') {
            return redirect()->route('client.checkout.success', $txn->id);
        }

        if ($txn->status === 'failed') {
            return redirect()->route('client.checkout.failed', $txn->id);
        }

        $qrImageUrl = null;
        if (! empty($txn->qr_code)) {
            $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='.urlencode($txn->qr_code);
        }

        return view('client.checkout.pending', [
            'transaction' => $txn,
            'qrImageUrl' => $qrImageUrl,
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $transaction = $this->checkoutService->handleWebhook($request->all());
        } catch (WebhookException $exception) {
            report($exception);

            return response()->json([
                'message' => 'Invalid webhook signature.',
            ], 400);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Webhook processing error.',
            ], 500);
        }

        return response()->json([
            'message' => 'Webhook processed.',
            'transaction_id' => $transaction?->id,
        ]);
    }
}
