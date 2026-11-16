<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Stream or download the payment receipt (Kwitansi) PDF.
     * Only available when the order payment has been successfully completed.
     */
    public function __invoke(Request $request, Order $order)
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        // Check ownership or admin
        abort_unless($isAdmin || ($user && $order->user_id === $user->id), 403);

        // Hanya bisa diunduh saat sudah sukses pembayaran
        $isPaid = $order->payment_status === 'paid' || $order->status === 'paid' || $order->paid_at !== null;
        abort_unless($isAdmin || $isPaid, 403, 'Kwitansi hanya dapat diunduh setelah pembayaran berhasil.');

        $order->load(['items', 'user']);

        $pdf = Pdf::loadView('pdf.receipt', [
            'order' => $order,
            'receipt' => $order->receiptData(),
        ])->setPaper('a4', 'landscape');

        $receiptNo = $order->receipt_no ?: $order->order_no;
        $filename = 'kwitansi-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $receiptNo).'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
