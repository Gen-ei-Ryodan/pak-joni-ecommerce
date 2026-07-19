<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private OrderService $orderService,
    ) {}

    public function index(Request $request)
    {
        Log::info('[ORDER] index', [
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'session_id' => $request->session()->getId(),
        ]);
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
        Log::info('[ORDER] show', [
            'order_id' => $order->id,
            'order_user_id' => $order->user_id,
            'auth_user_id' => $request->user()?->id,
            'auth_user_email' => $request->user()?->email,
            'match' => $order->user_id === $request->user()?->id,
            'session_id' => $request->session()->getId(),
        ]);

        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load(['items.part', 'items.variant.part', 'shipment', 'payment']);

        $timeline = $this->buildTimeline($order);

        return view('buyer.orders.show', compact('order', 'timeline'));
    }

    public function confirmReceived(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
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
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $order->is_indent) {
            return back()->withErrors(['indent' => 'Bukan pesanan indent.']);
        }

        if ($order->indent_status !== 'waiting_payment') {
            return back()->withErrors(['indent' => 'Belum waktunya pelunasan.']);
        }

        // Simulate full payment for the remaining amount
        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'total' => $order->subtotal + $order->shipping_cost,
                'dp_amount' => $order->dp_amount,
                'remaining_amount' => 0,
                'indent_status' => 'paid_full',
                'status' => 'paid',
            ]);

            // Update payment record
            if ($payment = $order->payment) {
                $payment->update([
                    'amount' => $order->total,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);
            }
        });

        return redirect()->route('buyer.orders.show', $order)->with('status', 'Pelunasan berhasil! Pesanan akan segera diproses.');
    }

    private function buildTimeline(Order $order): array
    {
        $shippedLabel = $order->isDealerPickup() ? 'Siap Diambil' : 'Shipped';

        $tl = [];
        $tl[] = ['label' => 'Order Created', 'time' => $order->created_at, 'done' => true];
        $tl[] = ['label' => 'Awaiting Payment', 'time' => $order->created_at, 'done' => $order->status !== 'unpaid' || $order->payment_status === 'paid'];
        $tl[] = ['label' => 'Payment Successful', 'time' => $order->paid_at, 'done' => in_array($order->status, ['paid','processing','shipped','completed'])];
        $tl[] = ['label' => 'Processing', 'time' => $order->status === 'processing' ? $order->updated_at : null, 'done' => in_array($order->status, ['processing','shipped','completed'])];
        $tl[] = ['label' => $shippedLabel, 'time' => $order->shipped_at, 'done' => in_array($order->status, ['shipped','completed'])];
        $tl[] = ['label' => 'Completed', 'time' => $order->completed_at, 'done' => $order->status === 'completed'];

        if ($order->status === 'cancelled') {
            $tl[] = ['label' => 'Cancelled', 'time' => $order->cancelled_at, 'done' => true];
        }

        return $tl;
    }
}
