<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MotorColor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PartVariant;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
    ) {}

    public function address(Request $request)
    {
        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $addresses = Address::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);
        $selectedAddressId = (int) ($request->session()->get('checkout.address_id') ?? 0);

        return view('buyer.checkout.address', compact('cart', 'addresses', 'subtotal', 'selectedAddressId'));
    }

    public function setAddress(Request $request)
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $address = Address::query()->findOrFail((int) $validated['address_id']);

        if ($address->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->session()->put('checkout.address_id', $address->id);

        return redirect('/checkout/shipping');
    }

    public function shipping(Request $request)
    {
        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $addressId = (int) ($request->session()->get('checkout.address_id') ?? 0);
        if (! $addressId) {
            return redirect('/checkout')->with('status', 'Pilih alamat dulu.');
        }

        $address = Address::query()->where('user_id', $request->user()->id)->findOrFail($addressId);

        $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);
        $shippingSnapshot = $request->session()->get('checkout.shipping') ?? null;

        return view('buyer.checkout.shipping', compact('cart', 'address', 'subtotal', 'shippingSnapshot'));
    }

    public function setShipping(Request $request)
    {
        $validated = $request->validate([
            'courier' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $request->session()->put('checkout.shipping', [
            'courier' => $validated['courier'],
            'service' => $validated['service'],
            'shipping_cost' => (float) $validated['shipping_cost'],
        ]);

        return redirect('/checkout/payment');
    }

    public function payment(Request $request)
    {
        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $addressId = (int) ($request->session()->get('checkout.address_id') ?? 0);
        $shipping = $request->session()->get('checkout.shipping');

        if (! $addressId) {
            return redirect('/checkout')->with('status', 'Pilih alamat dulu.');
        }

        if (! $shipping) {
            return redirect('/checkout/shipping')->with('status', 'Pilih shipping dulu.');
        }

        $address = Address::query()->where('user_id', $request->user()->id)->findOrFail($addressId);

        $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);
        $shippingCost = (float) ($shipping['shipping_cost'] ?? 0);
        $total = $subtotal + $shippingCost;

        // Check if any item is indent
        $hasIndent = $this->hasIndentItems($cart);
        $dpAmount = 0;
        $remainingAmount = 0;

        if ($hasIndent) {
            $dpAmount = round($subtotal * 0.5);
            $remainingAmount = $subtotal - $dpAmount;
        }

        return view('buyer.checkout.payment', compact(
            'cart', 'address', 'shipping', 'subtotal', 'shippingCost', 'total',
            'hasIndent', 'dpAmount', 'remainingAmount'
        ));
    }

    public function placeOrder(Request $request)
    {
        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $addressId = (int) ($request->session()->get('checkout.address_id') ?? 0);
        $shipping = $request->session()->get('checkout.shipping');

        if (! $addressId || ! $shipping) {
            return redirect('/checkout')->with('status', 'Checkout belum lengkap.');
        }

        $address = Address::query()->where('user_id', $request->user()->id)->findOrFail($addressId);

        return DB::transaction(function () use ($request, $cart, $address, $shipping) {
            // Validate stock for part variants only
            foreach ($cart->items as $it) {
                if ($it->itemable_type === PartVariant::class) {
                    $variant = PartVariant::lockForUpdate()->find($it->itemable_id);
                    if (! $variant) continue;
                    if ($variant->stock < $it->quantity) {
                        return redirect('/cart')->withErrors(['stock' => 'Stock tidak cukup untuk '.$it->product_name]);
                    }
                }
            }

            $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);
            $shippingCost = (float) ($shipping['shipping_cost'] ?? 0);
            $total = $subtotal + $shippingCost;

            $hasIndent = $this->hasIndentItems($cart);
            $dpAmount = 0;
            $remainingAmount = 0;
            $isIndent = $hasIndent;

            if ($hasIndent) {
                $dpAmount = round($subtotal * 0.5);
                $remainingAmount = $subtotal - $dpAmount;
                $total = $dpAmount + $shippingCost;
            }

            $indentStatus = $hasIndent ? 'waiting_stock' : null;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_no' => $this->newOrderNo(),
                'status' => 'unpaid',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'is_indent' => $isIndent,
                'indent_status' => $indentStatus,
                'address_snapshot' => [
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'address_line1' => $address->address_line1,
                    'address_line2' => $address->address_line2,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postal_code' => $address->postal_code,
                    'notes' => $address->notes,
                ],
                'shipping_snapshot' => [
                    'courier' => $shipping['courier'] ?? null,
                    'service' => $shipping['service'] ?? null,
                    'shipping_cost' => $shippingCost,
                ],
            ]);

            $this->paymentService->createPayment($order);

            foreach ($cart->items as $it) {
                // Decrement stock for part variants
                if ($it->itemable_type === PartVariant::class) {
                    $variant = PartVariant::lockForUpdate()->find($it->itemable_id);
                    if ($variant) {
                        $variant->stock = max(0, $variant->stock - $it->quantity);
                        $variant->save();
                    }
                }

                $partId = null;
                $sku = '';
                if ($it->itemable_type === PartVariant::class) {
                    $pv = PartVariant::with('part')->find($it->itemable_id);
                    if ($pv) {
                        $partId = $pv->part_id;
                        $sku = $pv->sku;
                    }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'part_id' => $partId,
                    'itemable_type' => $it->itemable_type,
                    'itemable_id' => $it->itemable_id,
                    'sku' => $sku,
                    'name' => $it->product_name,
                    'variant_name' => $it->variant_name,
                    'price' => $it->price_snapshot,
                    'quantity' => $it->quantity,
                    'line_total' => (float) $it->price_snapshot * (int) $it->quantity,
                ]);
            }

            $selectedIds = $request->session()->get('checkout.selected_ids', []);
            if (! empty($selectedIds)) {
                $cart->items()->whereIn('id', $selectedIds)->delete();
            } else {
                $cart->items()->delete();
            }
            $request->session()->forget('checkout');

            return redirect('/checkout/finish/'.$order->id);
        });
    }

    public function finish(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        $order->load('items');

        return view('buyer.checkout.finish', compact('order'));
    }

    private function hasIndentItems(Cart $cart): bool
    {
        foreach ($cart->items as $it) {
            if ($it->itemable_type === MotorColor::class) {
                $color = MotorColor::with('motor')->find($it->itemable_id);
                if ($color && $color->motor && $color->motor->stock_status === 'indent') {
                    return true;
                }
            }
            if ($it->itemable_type === PartVariant::class) {
                $variant = PartVariant::with('part')->find($it->itemable_id);
                if ($variant && $variant->part && $variant->part->stock_status === 'indent') {
                    return true;
                }
            }
        }
        return false;
    }

    private function cart(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function loadSelectedCart(Request $request): Cart
    {
        $cart = $this->cart($request)->load('items');
        
        // Load relations for each itemable type
        foreach ($cart->items as $item) {
            if ($item->itemable_type === PartVariant::class) {
                $item->loadMissing(['itemable.part']);
            } elseif ($item->itemable_type === MotorColor::class) {
                $item->loadMissing(['itemable.motor']);
            }
        }

        $selectedIds = $request->session()->get('checkout.selected_ids', []);

        if (! empty($selectedIds)) {
            $cart->items = $cart->items->filter(fn ($it) => in_array($it->id, $selectedIds));
        }

        return $cart;
    }

    private function newOrderNo(): string
    {
        $base = 'PJ'.now()->format('ymd');

        do {
            $suffix = Str::upper(Str::random(6));
            $no = $base.$suffix;
        } while (Order::query()->where('order_no', $no)->exists());

        return $no;
    }
}
