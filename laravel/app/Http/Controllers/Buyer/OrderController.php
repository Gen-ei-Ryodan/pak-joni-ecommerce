<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private OrderService $orderService,
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
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $order->load(['items.part', 'items.variant.part', 'shipment', 'payment']);

        $timeline = $this->buildTimeline($order);

        return view('buyer.orders.show', compact('order', 'timeline'));
    }

    public function confirmReceived(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if ($order->status !== 'shipped') {
            return back()->withErrors(['confirm' => 'Pesanan tidak dalam status dikirim.']);
        }

        $this->orderService->markAsCompleted($order);

        return redirect()->route('buyer.orders.show', $order)->with('status', 'Pesanan telah dikonfirmasi diterima.');
    }

    public function payRemaining(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        if (! $order->is_indent) {
            return back()->withErrors(['indent' => 'Bukan pesanan indent.']);
        }

        if ($order->indent_status !== 'waiting_payment') {
            return back()->withErrors(['indent' => 'Belum waktunya pelunasan.']);
        }

        // Redirect to order page which will show the Midtrans Snap button
        return redirect()->route('buyer.orders.show', $order);
    }

    private function buildTimeline(Order $order): array
    {
        $tl = [];
        $tl[] = ['label' => 'Order Created', 'time' => $order->created_at, 'done' => true];
        $tl[] = ['label' => 'Awaiting Payment', 'time' => $order->created_at, 'done' => $order->status !== 'unpaid' || $order->payment_status === 'paid'];
        $tl[] = ['label' => 'Payment Successful', 'time' => $order->paid_at, 'done' => in_array($order->status, ['paid','processing','shipped','completed'])];
        $tl[] = ['label' => 'Processing', 'time' => $order->status === 'processing' ? $order->updated_at : null, 'done' => in_array($order->status, ['processing','shipped','completed'])];
        $tl[] = ['label' => 'Shipped', 'time' => $order->shipped_at, 'done' => in_array($order->status, ['shipped','completed'])];
        $tl[] = ['label' => 'Completed', 'time' => $order->completed_at, 'done' => $order->status === 'completed'];

        if ($order->status === 'cancelled') {
            $tl[] = ['label' => 'Cancelled', 'time' => $order->cancelled_at, 'done' => true];
        }

        return $tl;
    }
}
