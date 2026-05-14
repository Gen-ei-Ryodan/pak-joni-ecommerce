<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $orders = Order::query()
            ->with(['user'])
            ->when($q !== '', fn ($query) => $query->where('order_no', 'like', '%'.$q.'%'))
            ->when($status !== '' && in_array($status, Order::STATUSES), fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'q', 'status'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'payment', 'shipment']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:mark_paid,process,ship,complete,cancel'],
            'courier' => ['nullable', 'required_if:action,ship', 'string', 'max:255'],
            'receipt' => ['nullable', 'required_if:action,ship', 'string', 'max:255'],
        ]);

        $success = false;
        $message = '';

        switch ($validated['action']) {
            case 'mark_paid':
                $success = $this->orderService->markAsPaid($order);
                $message = 'Order ditandai sebagai paid.';
                break;
            case 'process':
                $success = $this->orderService->processOrder($order);
                $message = 'Order mulai diproses.';
                break;
            case 'ship':
                $success = $this->orderService->markAsShipped(
                    $order,
                    $validated['courier'] ?? '',
                    $validated['receipt'] ?? ''
                );
                $message = 'Order dikirim.';
                break;
            case 'complete':
                $success = $this->orderService->markAsCompleted($order);
                $message = 'Order selesai.';
                break;
            case 'cancel':
                $success = $this->orderService->cancelOrder($order);
                $message = 'Order dibatalkan.';
                break;
        }

        if (! $success) {
            return back()->withErrors(['status' => 'Transisi status tidak valid.']);
        }

        return redirect()->route('admin.orders.show', $order)->with('status', $message);
    }
}
