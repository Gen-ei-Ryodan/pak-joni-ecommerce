<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    /**
     * Handle incoming Midtrans notification (server-to-server callback).
     * Midtrans will POST to this URL after payment status changes.
     */
    public function notification(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans notification received', ['payload' => $payload]);

        $result = $this->paymentService->processNotification($payload);

        if (! $result['success']) {
            Log::error('Midtrans notification processing failed', $result);
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Handle finish redirect after Midtrans payment.
     * Called when Midtrans redirects user back to the app.
     */
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id');
        $transactionStatus = $request->query('transaction_status');

        if ($orderId && $transactionStatus) {
            $this->paymentService->midtransCallbackHandler([
                'order_id' => $orderId,
                'transaction_status' => $transactionStatus,
            ]);
        }

        if ($orderId) {
            return redirect()->route('buyer.orders.show', $orderId);
        }

        return redirect()->route('buyer.dashboard');
    }

    /**
     * Handle unfinish redirect (user closed the popup / payment not completed).
     */
    public function unfinish(Request $request)
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            return redirect()->route('buyer.orders.show', $orderId)->with('status', 'Pembayaran belum selesai. Silakan coba lagi.');
        }

        return redirect()->route('buyer.dashboard');
    }

    /**
     * Handle error redirect from Midtrans.
     */
    public function error(Request $request)
    {
        $orderId = $request->query('order_id');

        if ($orderId) {
            return redirect()->route('buyer.orders.show', $orderId)->withErrors(['payment' => 'Terjadi kesalahan pembayaran. Silakan coba lagi.']);
        }

        return redirect()->route('buyer.dashboard');
    }
}
