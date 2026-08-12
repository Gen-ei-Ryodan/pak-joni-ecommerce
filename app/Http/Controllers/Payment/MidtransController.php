<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
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
     *
     * NOTE: This is a UX-only redirect. The order is NEVER marked as paid here
     * because the browser redirect and its query parameters are not trustworthy.
     * Payment state is only updated via the signed server-to-server notification
     * webhook or the authenticated, server-side status endpoint.
     */
    public function finish(Request $request)
    {
        if ($request->user()) {
            $orderId = $request->query('order_id');

            if ($orderId) {
                $order = Order::query()->where('order_no', $orderId)->where('user_id', $request->user()->id)->first();

                if ($order) {
                    return redirect()->route('buyer.orders.show', $order);
                }
            }
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

    /**
     * Generate and return Snap token for the order (AJAX).
     * Used by the "Bayar Sekarang" button in frontend.
     */
    public function snapToken(Order $order, Request $request)
    {
        if ($order->user_id != $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->load('items');

        $token = $this->paymentService->getSnapToken($order);

        if (! $token) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal mendapatkan token pembayaran. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'snap_token' => $token,
            'client_key' => config('services.midtrans.client_key'),
        ]);
    }

    /**
     * Check payment status manually via Midtrans API.
     * Used by the "Cek Status" button in frontend.
     */
    public function status(Order $order, Request $request)
    {
        if ($order->user_id != $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'paid' => true,
                'status' => 'paid',
                'payment_status' => $order->payment_status,
            ]);
        }

        if ($order->payment_status === 'failed') {
            return response()->json([
                'paid' => false,
                'status' => 'failed',
                'payment_status' => $order->payment_status,
            ]);
        }

        if ($order->payment_status === 'expired') {
            return response()->json([
                'paid' => false,
                'status' => 'expired',
                'payment_status' => $order->payment_status,
            ]);
        }

        // If payment is pending, check status directly from Midtrans
        try {
            $result = $this->paymentService->checkStatusFromMidtrans($order);

            if ($result['paid'] ?? false) {
                return response()->json([
                    'paid' => true,
                    'status' => 'paid',
                    'transaction_status' => $result['transaction_status'] ?? 'settlement',
                ]);
            }

            return response()->json([
                'paid' => false,
                'status' => $order->status,
                'transaction_status' => $result['transaction_status'] ?? 'pending',
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans status check failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'paid' => $order->payment_status === 'paid',
                'status' => $order->status,
                'payment_status' => $order->payment_status,
            ]);
        }
    }
}
