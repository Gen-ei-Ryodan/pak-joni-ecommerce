<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PartsInvoiceController extends Controller
{
    /**
     * Stream or download the Spareparts Invoice (Faktur Sukucadang) PDF.
     */
    public function __invoke(Request $request, Order $order)
    {
        $user = $request->user();
        $isAdmin = (bool) $user?->isAdmin();

        abort_unless($isAdmin || ($user && $order->user_id === $user->id), 403);

        $order->load(['items', 'user']);

        $pdf = Pdf::loadView('pdf.parts-invoice', [
            'order' => $order,
        ])->setPaper('a4', 'portrait');

        $filename = 'faktur-sukucadang-'.preg_replace('/[^A-Za-z0-9_-]/', '-', $order->order_no).'.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }
}
