<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\PartVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->cart($request)->load(['items.variant.part.category']);

        $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);

        return view('buyer.cart.index', compact('cart', 'subtotal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:part_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $variant = PartVariant::query()->with('part')->findOrFail((int) $validated['variant_id']);

        return DB::transaction(function () use ($request, $validated, $variant) {
            $cart = $this->cart($request);

            $item = CartItem::query()->where('cart_id', $cart->id)->where('part_variant_id', $variant->id)->first();
            $qty = (int) $validated['quantity'];

            if ($item) {
                $item->quantity = min(99, $item->quantity + $qty);
                $item->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'part_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'price_snapshot' => $variant->price,
                ]);
            }

            return redirect('/cart')->with('status', 'Item masuk ke cart.');
        });
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        if ($cartItem->cart->user_id !== $request->user()->id) {
            abort(403);
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

    private function cart(Request $request): Cart
    {
        return Cart::firstOrCreate(['user_id' => $request->user()->id]);
    }
}

