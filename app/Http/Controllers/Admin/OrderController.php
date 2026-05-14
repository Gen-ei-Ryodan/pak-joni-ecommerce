<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $orders = Order::query()
            ->with(['user'])
            ->when($q !== '', fn ($query) => $query->where('order_no', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'q'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items', 'payment', 'shipment']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,expired,cancelled,shipped,completed'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $order->status = $validated['status'];
        $order->save();

        if ($validated['tracking_number'] ?? null) {
            $order->shipment()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'tracking_number' => $validated['tracking_number'],
                    'status' => $validated['status'] === 'shipped' ? 'shipped' : 'pending',
                ]
            );
        }

        return redirect()->route('admin.orders.show', $order)->with('status', 'Order berhasil diupdate.');
    }
}
