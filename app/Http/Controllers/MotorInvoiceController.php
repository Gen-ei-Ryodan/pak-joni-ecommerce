<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MotorInvoiceController extends Controller
{
    /**
     * Stream (or download with ?download=1) the motor purchase invoice PDF.
     * Accessible by admins and by the order's buyer once the invoice is filled in.
     */
    public function __invoke(Request $request, Order $order)
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        abort_unless($isAdmin || ($user && $order->user_id === $user->id), 403);
        abort_unless($isAdmin || (bool) $order->invoice_no, 403, 'Faktur motor belum tersedia.');

        $order->load(['items', 'user']);

        $pdf = Pdf::loadView('pdf.motor-invoice', [
            'order' => $order,
            'invoice' => $order->motorInvoiceData(),
        ])->setPaper('a4', 'landscape');

        $filename = 'faktur-motor-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $order->invoice_no ?: $order->order_no).'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
