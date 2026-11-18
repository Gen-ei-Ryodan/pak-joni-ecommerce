<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Stream or download the payment receipt (Kwitansi) PDF.
     * Accessible for DP receipt after DP is paid, and Pelunasan receipt after full payment.
     */
    public function __invoke(Request $request, Order $order)
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        // Check ownership or admin
        abort_unless($isAdmin || ($user && $order->user_id === $user->id), 403);

        $type = $request->query('type', 'auto'); // 'dp', 'full', or 'auto'

        if ($type === 'dp') {
            $canDownloadDp = $isAdmin || $order->isDpPaid() || $order->isFullyPaid() || $order->paid_at !== null;
            abort_unless($canDownloadDp, 403, 'Kwitansi DP hanya dapat diunduh setelah pembayaran DP berhasil.');
        } else {
            $isPaid = $isAdmin || $order->isFullyPaid() || (! $order->isCashVehicle() && ($order->payment_status === 'paid' || $order->status === 'paid' || $order->paid_at !== null));
            abort_unless($isPaid, 403, 'Kwitansi Pelunasan hanya dapat diunduh setelah pelunasan berhasil.');
        }

        $order->load(['items', 'user']);

        $receiptData = $order->receiptData($type);

        $pdf = Pdf::loadView('pdf.receipt', [
            'order' => $order,
            'receipt' => $receiptData,
        ])->setPaper('a4', 'landscape');

        $receiptNo = $receiptData['receipt_no'] ?: $order->order_no;
        $filename = 'kwitansi-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $receiptNo).'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
