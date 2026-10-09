<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OcbcPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OcbcController extends Controller
{
    public function __construct(
        private OcbcPaymentService $paymentService,
    ) {}

    public function notify(Request $request)
    {
        $result = $this->paymentService->processNotification(
            $request->all(),
            [
                'x-signature' => (string) $request->header('X-SIGNATURE'),
                'x-timestamp' => (string) $request->header('X-TIMESTAMP'),
            ],
        );

        if (! $result['success']) {
            Log::warning('OCBC notification rejected', [
                'message' => $result['message'],
                'external_id' => $request->header('X-EXTERNAL-ID'),
            ]);

            return response()->json([
                'responseCode' => '401',
                'responseMessage' => $result['message'],
            ], 401);
        }

        return response()->json([
            'responseCode' => '2005200',
            'responseMessage' => 'Successful',
        ]);
    }

    public function qr(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'unpaid' || $order->payment_status !== 'pending') {
            return response()->json(['error' => 'Order tidak menunggu pembayaran.'], 422);
        }

        try {
            return response()->json($this->paymentService->generateQr($order));
        } catch (\Throwable $e) {
            Log::error('OCBC QR generation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Gagal membuat QR pembayaran. Silakan coba lagi.',
            ], 502);
        }
    }

    public function status(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['paid' => true, 'status' => 'paid']);
        }

        try {
            return response()->json($this->paymentService->query($order));
        } catch (\Throwable $e) {
            Log::error('OCBC payment status failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'paid' => false,
                'status' => 'pending',
            ]);
        }
    }
}
