<?php

namespace App\Filament\Exports;

use App\Models\Order;
use OpenSpout\Writer\XLSX\Writer;

class OrderExport
{
    public function export(): string
    {
        $fileName = 'orders_export_' . now()->format('Y-m-d_His') . '.xlsx';
        $filePath = storage_path('app/public/exports/' . $fileName);

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = Writer::createFromFile($filePath);
        $writer->openToFile($filePath);

        // Header row
        $writer->addRow([
            'Order No', 'Customer', 'Email', 'Phone',
            'Status', 'Payment Status', 'Indent Status',
            'Subtotal', 'Shipping Cost', 'DP Amount', 'Remaining', 'Total',
            'Courier', 'Service', 'Receipt',
            'Address', 'City', 'Province',
            'Date', 'Paid At', 'Shipped At'
        ]);

        Order::query()
            ->with('user')
            ->orderByDesc('id')
            ->chunk(200, function ($orders) use ($writer) {
                foreach ($orders as $order) {
                    $addr = $order->address_snapshot ?? [];

                    $writer->addRow([
                        $order->order_no,
                        $order->user?->name ?? 'N/A',
                        $order->user?->email ?? 'N/A',
                        $addr['phone'] ?? '-',
                        $order->status,
                        $order->payment_status,
                        $order->indent_status ?? '-',
                        $order->subtotal,
                        $order->shipping_cost,
                        $order->dp_amount ?? 0,
                        $order->remaining_amount ?? 0,
                        $order->total,
                        $order->shipping_courier ?? '-',
                        $order->shipping_receipt ?? '-',
                        ($addr['address_line1'] ?? '') . ' ' . ($addr['address_line2'] ?? ''),
                        $addr['city'] ?? '-',
                        $addr['province'] ?? '-',
                        $order->created_at?->format('Y-m-d H:i') ?? '-',
                        $order->paid_at ? \Carbon\Carbon::parse($order->paid_at)->format('Y-m-d H:i') : '-',
                        $order->shipped_at ? \Carbon\Carbon::parse($order->shipped_at)->format('Y-m-d H:i') : '-',
                    ]);
                }
            });

        $writer->close();

        return $fileName;
    }
}
