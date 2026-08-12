<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ItemColor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PartVariant;
use App\Services\BiteshipService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private BiteshipService $biteshipService,
    ) {}

    public function address(Request $request)
    {
        Log::info('[CHECKOUT] address', [
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'session_id' => $request->session()->getId(),
        ]);
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
            'address_id' => ['required', 'integer'],
        ]);

        $addressId = (int) $validated['address_id'];

        Log::info('[CHECKOUT] setAddress', [
            'address_id' => $addressId,
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'session_id' => $request->session()->getId(),
        ]);

        // address_id = 0 means dealer pickup
        if ($addressId === 0) {
            $request->session()->put('checkout.dealer_pickup', true);
            $request->session()->forget('checkout.address_id');
            $request->session()->forget('checkout.shipping');

            return redirect('/checkout/payment');
        }

        $address = Address::query()->findOrFail($addressId);

        Log::info('[CHECKOUT] setAddress check', [
            'address_user_id' => $address->user_id,
            'auth_user_id' => $request->user()?->id,
            'match' => $address->user_id == $request->user()?->id,
        ]);

        if ($address->user_id != $request->user()->id) {
            return redirect()->back()->withErrors(['address' => 'Alamat tidak valid. Silakan pilih alamat lain.']);
        }

        $request->session()->put('checkout.address_id', $address->id);
        $request->session()->forget('checkout.dealer_pickup');

        return redirect('/checkout/shipping');
    }

    public function shipping(Request $request)
    {
        // If dealer pickup, skip shipping step
        if ($request->session()->get('checkout.dealer_pickup')) {
            return redirect('/checkout/payment');
        }

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

        $hasIndent = $this->hasIndentItems($cart);
        $indentCalc = $hasIndent ? $this->calculateIndentAmounts($cart) : ['dp' => 0, 'remaining' => 0];
        $dpAmount = $indentCalc['dp'];
        $remainingAmount = $indentCalc['remaining'];

        return view('buyer.checkout.shipping', compact('cart', 'address', 'subtotal', 'shippingSnapshot', 'hasIndent', 'dpAmount', 'remainingAmount'));
    }

    public function setShipping(Request $request)
    {
        $validated = $request->validate([
            'courier' => ['required', 'string', 'max:255'],
            'service' => ['required', 'string', 'max:255'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'courier_name' => ['nullable', 'string', 'max:255'],
            'service_name' => ['nullable', 'string', 'max:255'],
        ]);

        $addressId = (int) ($request->session()->get('checkout.address_id') ?? 0);
        if (! $addressId) {
            return redirect('/checkout')->with('status', 'Pilih alamat dulu.');
        }

        $address = Address::query()->where('user_id', $request->user()->id)->find($addressId);
        if (! $address || empty($address->postal_code)) {
            return redirect()->back()->withErrors(['shipping' => 'Alamat tidak valid.']);
        }

        // Security: never trust client-provided shipping_cost. Re-query the carrier
        // rates server-side and use the quoted price for the selected service.
        $cart = $this->loadSelectedCart($request);
        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $serverCost = $this->serverShippingCost($cart, $address, $validated['courier'], $validated['service']);

        if ($serverCost === null) {
            return redirect()->back()->withErrors(['shipping' => 'Ongkos kirim tidak valid. Silakan pilih ulang kurir.']);
        }

        $request->session()->put('checkout.shipping', [
            'courier' => $validated['courier'],
            'service' => $validated['service'],
            'shipping_cost' => $serverCost,
            'courier_name' => $validated['courier_name'] ?? $validated['courier'],
            'service_name' => $validated['service_name'] ?? $validated['service'],
        ]);

        return redirect('/checkout/payment');
    }

    /**
     * Query the carrier rate server-side and return the quoted price for the
     * selected courier/service, or null when no match is found.
     */
    private function serverShippingCost(Cart $cart, Address $address, string $courier, string $service): ?float
    {
        try {
            $items = $this->biteshipService->buildItemsFromCart($cart->items);
            $result = $this->biteshipService->getRates(
                items: $items,
                destinationPostalCode: $address->postal_code,
                originPostalCode: null, // auto from store address
                couriers: strtoupper($courier),
            );
        } catch (\Throwable $e) {
            Log::warning('Server shipping rate check failed', ['error' => $e->getMessage()]);
            return null;
        }

        if (! ($result['success'] ?? false)) {
            return null;
        }

        foreach (($result['pricing'] ?? []) as $rate) {
            if (($rate['courier_code'] ?? '') === $courier
                && ($rate['courier_service_code'] ?? '') === $service) {
                return (float) ($rate['price'] ?? 0);
            }
        }

        return null;
    }

    public function rates(Request $request)
    {
        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'Cart kosong.']);
        }

        $addressId = (int) ($request->session()->get('checkout.address_id') ?? 0);
        if (! $addressId) {
            return response()->json(['success' => false, 'error' => 'Pilih alamat dulu.']);
        }

        $address = Address::query()->where('user_id', $request->user()->id)->find($addressId);
        if (! $address) {
            return response()->json(['success' => false, 'error' => 'Alamat tidak ditemukan.']);
        }

        if (empty($address->postal_code)) {
            return response()->json(['success' => false, 'error' => 'Kode pos alamat tujuan belum diisi.']);
        }

        $items = $this->biteshipService->buildItemsFromCart($cart->items);
        $couriers = $request->query('couriers');

        $result = $this->biteshipService->getRates(
            items: $items,
            destinationPostalCode: $address->postal_code,
            originPostalCode: null, // auto from store address
            couriers: $couriers,
        );

        return response()->json($result);
    }

    public function payment(Request $request)
    {
        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $isDealerPickup = $request->session()->get('checkout.dealer_pickup');

        if ($isDealerPickup) {
            // Dealer pickup: no shipping, no address needed
            $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);
            $shippingCost = 0;
            $total = $subtotal;

            $hasIndent = $this->hasIndentItems($cart);
            $indentCalc = $hasIndent ? $this->calculateIndentAmounts($cart) : ['dp' => 0, 'remaining' => 0];
            $dpAmount = $indentCalc['dp'];
            $remainingAmount = $indentCalc['remaining'];

            $shipping = null;
            $address = null;

            return view('buyer.checkout.payment', compact(
                'cart', 'address', 'shipping', 'subtotal', 'shippingCost', 'total',
                'hasIndent', 'dpAmount', 'remainingAmount', 'isDealerPickup'
            ));
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
        $indentCalc = $hasIndent ? $this->calculateIndentAmounts($cart) : ['dp' => 0, 'remaining' => 0];
        $dpAmount = $indentCalc['dp'];
        $remainingAmount = $indentCalc['remaining'];

        return view('buyer.checkout.payment', compact(
            'cart', 'address', 'shipping', 'subtotal', 'shippingCost', 'total',
            'hasIndent', 'dpAmount', 'remainingAmount', 'isDealerPickup'
        ));
    }

    public function placeOrder(Request $request)
    {
        Log::info('[CHECKOUT] placeOrder', [
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'session_id' => $request->session()->getId(),
            'is_dealer_pickup' => $request->session()->get('checkout.dealer_pickup'),
            'address_id' => $request->session()->get('checkout.address_id'),
        ]);

        $cart = $this->loadSelectedCart($request);

        if ($cart->items->isEmpty()) {
            return redirect('/cart')->with('status', 'Cart masih kosong.');
        }

        $isDealerPickup = $request->session()->get('checkout.dealer_pickup');
        $addressId = (int) ($request->session()->get('checkout.address_id') ?? 0);
        $shipping = $request->session()->get('checkout.shipping');

        if ($isDealerPickup) {
            $address = null;
        } else {
            if (! $addressId || ! $shipping) {
                return redirect('/checkout')->with('status', 'Checkout belum lengkap.');
            }
            $address = Address::query()->where('user_id', $request->user()->id)->findOrFail($addressId);
        }

        $result = DB::transaction(function () use ($request, $cart, $address, $shipping, $isDealerPickup) {
            // Validate stock for part variants only (exclude indent quantity)
            foreach ($cart->items as $it) {
                if ($it->itemable_type === PartVariant::class) {
                    $variant = PartVariant::lockForUpdate()->find($it->itemable_id);
                    if (! $variant) continue;
                    $readyQty = max(0, $it->quantity - (int)($it->indent_quantity ?? 0));
                    if ($variant->stock < $readyQty) {
                        return redirect('/cart')->withErrors(['stock' => 'Stock tidak cukup untuk '.$it->product_name.' (ready: '.$variant->stock.')']);
                    }
                }
            }

            $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);

            if ($isDealerPickup) {
                $shippingCost = 0;
                $shippingType = Order::SHIPPING_TYPE_DEALER_PICKUP;
                $addressSnapshot = [];
                $shippingSnapshot = [
                    'type' => 'dealer_pickup',
                    'label' => 'Ambil di Dealer',
                ];
            } else {
                $shippingCost = (float) ($shipping['shipping_cost'] ?? 0);
                $shippingType = Order::SHIPPING_TYPE_COURIER;
                $addressSnapshot = [
                    'label' => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone' => $address->phone,
                    'address_line1' => $address->address_line1,
                    'address_line2' => $address->address_line2,
                    'city' => $address->city,
                    'province' => $address->province,
                    'postal_code' => $address->postal_code,
                    'notes' => $address->notes,
                ];
                $shippingSnapshot = [
                    'courier' => $shipping['courier'] ?? null,
                    'service' => $shipping['service'] ?? null,
                    'shipping_cost' => $shippingCost,
                ];
            }

            $hasIndent = $this->hasIndentItems($cart);
            $indentCalc = $hasIndent ? $this->calculateIndentAmounts($cart) : ['dp' => 0, 'remaining' => 0];
            $dpAmount = $indentCalc['dp'];
            $remainingAmount = $indentCalc['remaining'];
            $isIndent = $hasIndent;

            // Total = full subtotal (minus remaining) + shipping
            // Buyer pays: ready items full + indent DP + shipping
            // Remaining is paid later when stock arrives
            $total = $subtotal - $remainingAmount + $shippingCost;

            $indentStatus = $hasIndent ? 'waiting_stock' : null;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_no' => $this->newOrderNo(),
                'status' => 'unpaid',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'shipping_type' => $shippingType,
                'total' => $total,
                'dp_amount' => $dpAmount,
                'remaining_amount' => $remainingAmount,
                'is_indent' => $isIndent,
                'indent_status' => $indentStatus,
                'address_snapshot' => $addressSnapshot,
                'shipping_snapshot' => $shippingSnapshot,
            ]);

            $this->paymentService->createPayment($order);

            foreach ($cart->items as $it) {
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
                    'indent_quantity' => (int) ($it->indent_quantity ?? 0),
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

            return $order;
        });

        return redirect('/checkout/finish/'.$result->id);
    }

    public function finish(Request $request, Order $order)
    {
        Log::info('[CHECKOUT] finish', [
            'order_id' => $order->id,
            'order_user_id' => $order->user_id,
            'auth_user_id' => $request->user()?->id,
            'auth_user_email' => $request->user()?->email,
            'match' => $order->user_id == $request->user()?->id,
            'session_id' => $request->session()->getId(),
        ]);

        if ($order->user_id != $request->user()->id) {
            return redirect('/my/orders')->withErrors(['order' => 'Pesanan tidak ditemukan.']);
        }

        $order->load('items');

        // Generate Midtrans Snap token
        $snapToken = $this->paymentService->getSnapToken($order);
        $clientKey = config('services.midtrans.client_key');

        return view('buyer.checkout.finish', compact('order', 'snapToken', 'clientKey'));
    }

    /**
     * Calculate DP & remaining based on indent_quantity (not whole subtotal).
     * Returns ['dp' => int, 'remaining' => int, 'indent_subtotal' => float]
     */
    private function calculateIndentAmounts($cart): array
    {
        $indentSubtotal = 0.0;
        foreach ($cart->items as $it) {
            $indentQty = (int) ($it->indent_quantity ?? 0);
            if ($indentQty > 0) {
                $indentSubtotal += (float) $it->price_snapshot * $indentQty;
            }
        }

        // Also check underlying product stock_status
        foreach ($cart->items as $it) {
            $indentQty = (int) ($it->indent_quantity ?? 0);
            if ($indentQty > 0) continue; // already counted

            if ($it->itemable_type === ItemColor::class) {
                $color = ItemColor::with('item')->find($it->itemable_id);
                if ($color && $color->item && $color->item->stock_status === 'indent') {
                    $indentSubtotal += (float) $it->price_snapshot * (int) $it->quantity;
                }
            }
            if ($it->itemable_type === PartVariant::class) {
                $variant = PartVariant::with('part')->find($it->itemable_id);
                if ($variant && $variant->part && $variant->part->stock_status === 'indent') {
                    $indentSubtotal += (float) $it->price_snapshot * (int) $it->quantity;
                }
            }
        }

        $dp = (int) round($indentSubtotal * 0.5);
        return [
            'dp' => $dp,
            'remaining' => (int) round($indentSubtotal) - $dp,
            'indent_subtotal' => $indentSubtotal,
        ];
    }

    private function hasIndentItems(Cart $cart): bool
    {
        foreach ($cart->items as $it) {
            if ((int) ($it->indent_quantity ?? 0) > 0) {
                return true;
            }
            if ($it->itemable_type === ItemColor::class) {
                $color = ItemColor::with('item')->find($it->itemable_id);
                if ($color && $color->item && $color->item->stock_status === 'indent') {
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
            } elseif ($item->itemable_type === ItemColor::class) {
                $item->loadMissing(['itemable.item']);
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
