<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SpkController extends Controller
{
    /**
     * Stream or download the SPK (Surat Pemesanan Motor) PDF.
     * Accessible by admins and by the buyer once verified and DP is paid.
     */
    public function __invoke(Request $request, Order $order)
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        abort_unless($isAdmin || ($user && $order->user_id === $user->id), 403);
        abort_unless($isAdmin || (bool) $order->is_admin_verified, 403, 'Dokumen belum diverifikasi oleh admin dealer.');

        // Untuk order cash motor: SPK dapat diunduh setelah DP berhasil dibayar
        if (! $isAdmin && $order->isCashVehicle()) {
            $canDownloadSpk = $order->isDpPaid() || $order->isFullyPaid() || $order->paid_at !== null;
            abort_unless($canDownloadSpk, 403, 'SPK hanya dapat diunduh setelah pembayaran DP berhasil.');
        }

        $order->load(['items', 'user']);

        $pdf = Pdf::loadView('pdf.spk', [
            'order' => $order,
            'spk' => $order->spkData(),
        ])->setPaper('a4', 'landscape');

        $spkNo = $order->spk_no ?: $order->order_no;
        $filename = 'spk-motor-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $spkNo).'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
