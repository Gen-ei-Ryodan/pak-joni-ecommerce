<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MotorColor;
use App\Models\PartVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->cart($request)->load(['items.itemable.motor.brand', 'items.itemable.part.category']);

        $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);

        return view('buyer.cart.index', compact('cart', 'subtotal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'itemable_type' => ['required', 'string', 'in:part_variant,motor_color'],
            'itemable_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $type = $validated['itemable_type'];
        $id = (int) $validated['itemable_id'];
        $qty = (int) $validated['quantity'];

        if ($type === 'part_variant') {
            return $this->addPartVariant($request, $id, $qty);
        }

        if ($type === 'motor_color') {
            return $this->addMotorColor($request, $id, $qty);
        }

        return back()->withErrors(['type' => 'Invalid item type.']);
    }

    private function addPartVariant(Request $request, int $variantId, int $qty)
    {
        $variant = PartVariant::query()->with('part')->findOrFail($variantId);

        if ($variant->stock < 1) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Out of stock.'], 422)
                : back()->withErrors(['stock' => 'Out of stock.']);
        }

        $result = DB::transaction(function () use ($request, $variant, $qty) {
            $cart = $this->cart($request);

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('itemable_type', PartVariant::class)
                ->where('itemable_id', $variant->id)
                ->first();

            if ($item) {
                $newQty = $item->quantity + $qty;
                if ($newQty > $variant->stock) {
                    return ['success' => false, 'message' => 'Qty exceeds available stock ('.$variant->stock.').'];
                }
                $item->quantity = $newQty;
                $item->save();
            } else {
                if ($qty > $variant->stock) {
                    return ['success' => false, 'message' => 'Qty exceeds available stock ('.$variant->stock.').'];
                }
                CartItem::create([
                    'cart_id' => $cart->id,
                    'itemable_type' => PartVariant::class,
                    'itemable_id' => $variant->id,
                    'quantity' => $qty,
                    'price_snapshot' => $variant->price,
                    'product_name' => $variant->part->name,
                    'variant_name' => $variant->name,
                    'image_path' => $variant->part->thumbnail_path,
                ]);
            }
            $cartCount = $cart->items()->count();
            return ['success' => true, 'message' => 'Item added to cart.', 'cartCount' => $cartCount];
        });

        if ($request->expectsJson()) {
            return response()->json($result);
        }
        if ($result['success']) {
            return redirect('/cart')->with('status', $result['message']);
        }
        return back()->withErrors(['stock' => $result['message']]);
    }

    private function addMotorColor(Request $request, int $colorId, int $qty)
    {
        $color = MotorColor::query()->with('motor')->findOrFail($colorId);
        $motor = $color->motor;

        $result = DB::transaction(function () use ($request, $color, $motor, $qty) {
            $cart = $this->cart($request);

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('itemable_type', MotorColor::class)
                ->where('itemable_id', $color->id)
                ->first();

            if ($item) {
                $item->quantity = $item->quantity + $qty;
                $item->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'itemable_type' => MotorColor::class,
                    'itemable_id' => $color->id,
                    'quantity' => $qty,
                    'price_snapshot' => $motor->price ?? 0,
                    'product_name' => $motor->name,
                    'variant_name' => $color->name,
                    'image_path' => $color->image_path ?: $motor->thumbnail_path,
                ]);
            }
            $cartCount = $cart->items()->count();
            return ['success' => true, 'message' => 'Motor added to cart.', 'cartCount' => $cartCount];
        });

        if ($request->expectsJson()) {
            return response()->json($result);
        }
        if ($result['success']) {
            return redirect('/cart')->with('status', $result['message']);
        }
        return back()->withErrors(['stock' => $result['message']]);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403);
        }

        // Only validate stock for part variants
        if ($cartItem->itemable_type === PartVariant::class && $cartItem->itemable) {
            $variant = PartVariant::find($cartItem->itemable_id);
            if ($variant && (int) $validated['quantity'] > $variant->stock) {
                return back()->withErrors(['stock' => 'Qty melebihi stok tersedia ('.$variant->stock.').']);
            }
        }

        $cartItem->quantity = (int) $validated['quantity'];
        $cartItem->save();

        return redirect('/cart')->with('status', 'Qty diupdate.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403);
        }
        $cartItem->delete();
        return redirect('/cart')->with('status', 'Item dihapus dari cart.');
    }

    public function clear(Request $request)
    {
        $cart = $this->cart($request);
        $cart->items()->delete();
        return redirect('/cart')->with('status', 'Cart dikosongkan.');
    }

    public function checkoutSelected(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'string'],
        ]);

        $ids = explode(',', $validated['selected_ids']);
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn ($id) => $id > 0);

        if (empty($ids)) {
            return back()->withErrors(['select' => 'Pilih minimal satu item.']);
        }

        $cart = $this->cart($request);
        $validIds = $cart->items()->whereIn('id', $ids)->pluck('id')->toArray();

        if (empty($validIds)) {
            return back()->withErrors(['select' => 'Item tidak valid.']);
        }

        $request->session()->put('checkout.selected_ids', $validIds);
        return redirect('/checkout');
    }

    private function cart(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }
}
