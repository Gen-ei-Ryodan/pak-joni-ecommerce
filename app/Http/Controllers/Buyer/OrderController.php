<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $status = $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->when($status !== '' && in_array($status, Order::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where('order_no', 'like', '%'.$search.'%'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('buyer.orders.index', compact('orders', 'status', 'search'));
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['items', 'shipment', 'payment']);

        return view('buyer.orders.show', compact('order'));
    }

    public function simulatePayment(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'unpaid' || $order->payment_status !== 'pending') {
            return back()->withErrors(['payment' => 'Order tidak bisa disimulasikan.']);
        }

        $this->paymentService->simulateSuccessPayment($order);

        return redirect()->route('buyer.orders.show', $order)->with('status', 'Pembayaran berhasil disimulasikan.');
    }
}
