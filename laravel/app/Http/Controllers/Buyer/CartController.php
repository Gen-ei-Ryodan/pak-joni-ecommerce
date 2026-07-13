<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ItemColor;
use App\Models\PartVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->cart($request)->load('items');
        
        // Load relations for each itemable type
        foreach ($cart->items as $item) {
            if ($item->itemable_type === PartVariant::class) {
                $item->loadMissing(['itemable.part.category']);
            } elseif ($item->itemable_type === ItemColor::class) {
                $item->loadMissing(['itemable.item.brand']);
            }
        }

        $subtotal = $cart->items->sum(fn ($it) => (float) $it->price_snapshot * (int) $it->quantity);

        return view('buyer.cart.index', compact('cart', 'subtotal'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'itemable_type' => ['required', 'string', 'in:part_variant,item_color'],
            'itemable_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'indent_mode' => ['nullable', 'string', 'in:split,full'],
        ]);

        $type = $validated['itemable_type'];
        $id = (int) $validated['itemable_id'];
        $qty = (int) $validated['quantity'];
        $indentMode = $validated['indent_mode'] ?? null;

        if ($type === 'part_variant') {
            return $this->addPartVariant($request, $id, $qty, $indentMode);
        }

        if ($type === 'item_color') {
            return $this->addItemColor($request, $id, $qty);
        }

        return back()->withErrors(['type' => 'Invalid item type.']);
    }

    private function addPartVariant(Request $request, int $variantId, int $qty, ?string $indentMode = null)
    {
        $variant = PartVariant::query()->with('part')->findOrFail($variantId);

        $result = DB::transaction(function () use ($request, $variant, $qty, $indentMode) {
            $cart = $this->cart($request);
            $stock = (int) $variant->stock;

            $item = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('itemable_type', PartVariant::class)
                ->where('itemable_id', $variant->id)
                ->first();

            if ($item) {
                $newQty = $item->quantity + $qty;
                $item->quantity = $newQty;

                if ($stock <= 0) {
                    $item->indent_quantity = $newQty;
                } elseif ($newQty <= $stock) {
                    $item->indent_quantity = 0;
                } elseif ($indentMode === 'full') {
                    $item->indent_quantity = $newQty;
                } elseif ($indentMode === 'split') {
                    $item->indent_quantity = $newQty - $stock;
                } else {
                    // No indent_mode from frontend → use modal on cart page
                    $item->quantity = $stock;
                    $item->indent_quantity = 0;
                    $item->save();
                    return ['success' => false, 'message' => 'Qty exceeds available stock ('.$stock.').'];
                }

                $item->save();
                $msg = $this->indentStatusMessage($item, $stock);
            } else {
                if ($stock <= 0) {
                    $indentQty = $qty;
                } elseif ($qty <= $stock) {
                    $indentQty = 0;
                } elseif ($indentMode === 'full') {
                    $indentQty = $qty;
                } elseif ($indentMode === 'split') {
                    $indentQty = $qty - $stock;
                } else {
                    return ['success' => false, 'message' => 'Qty exceeds available stock ('.$stock.').'];
                }

                $item = CartItem::create([
                    'cart_id' => $cart->id,
                    'part_variant_id' => $variant->id,
                    'itemable_type' => PartVariant::class,
                    'itemable_id' => $variant->id,
                    'quantity' => $qty,
                    'indent_quantity' => $indentQty,
                    'price_snapshot' => $variant->price,
                    'product_name' => $variant->part->name,
                    'variant_name' => $variant->name,
                    'image_path' => $variant->part->thumbnail_path,
                ]);
                $msg = $this->indentStatusMessage($item, $stock);
            }

            $cartCount = $cart->items()->count();
            return ['success' => true, 'message' => $msg, 'cartCount' => $cartCount];
        });

        if ($request->expectsJson()) {
            return response()->json($result);
        }
        if ($result['success']) {
            return redirect('/cart')->with('status', $result['message']);
        }
        return back()->withErrors(['stock' => $result['message']]);
    }

    private function indentStatusMessage(CartItem $item, int $stock): string
    {
        $indent = (int) ($item->indent_quantity ?? 0);
        if ($indent <= 0) {
            return 'Item added to cart.';
        }
        $ready = $item->quantity - $indent;
        if ($ready > 0) {
            return "Item added. {$ready} ready + {$indent} indent (DP 50%).";
        }
        return "Item added. {$indent} item akan dipesan indent (DP 50%).";
    }

    private function addItemColor(Request $request, int $colorId, int $qty)
    {
        $color = ItemColor::query()->with('item')->findOrFail($colorId);
        $item = $color->item;

        $result = DB::transaction(function () use ($request, $color, $item, $qty) {
            $cart = $this->cart($request);

            $cartItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('itemable_type', ItemColor::class)
                ->where('itemable_id', $color->id)
                ->first();

            if ($cartItem) {
                $cartItem->quantity = $cartItem->quantity + $qty;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'itemable_type' => ItemColor::class,
                    'itemable_id' => $color->id,
                    'quantity' => $qty,
                    'price_snapshot' => $item->price ?? 0,
                    'product_name' => $item->name,
                    'variant_name' => $color->name,
                    'image_path' => $color->image_path ?: $item->thumbnail_path,
                ]);
            }
            $cartCount = $cart->items()->count();
            return ['success' => true, 'message' => 'Produk added to cart.', 'cartCount' => $cartCount];
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
            'quantity' => ['required', 'integer', 'min:1'],
            'indent_mode' => ['nullable', 'string', 'in:split,full'],
        ]);

        if ((int) $cartItem->cart->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $qty = (int) $validated['quantity'];

        // Only validate stock for part variants
        if ($cartItem->itemable_type === PartVariant::class) {
            $variant = PartVariant::find($cartItem->itemable_id);
            if ($variant && $qty > $variant->stock) {
                $stock = $variant->stock;
                $indentMode = $request->input('indent_mode');

                // If indent_mode provided, apply the choice
                if ($indentMode === 'split') {
                    // Ready sebanyak stock, sisanya indent
                    $cartItem->quantity = $stock;
                    $cartItem->indent_quantity = $qty - $stock;
                    $cartItem->save();
                    return redirect('/cart')->with('status', "Qty diupdate. {$stock} ready + ".($cartItem->indent_quantity)." indent.");
                }

                if ($indentMode === 'full') {
                    // Semua indent
                    $cartItem->quantity = $qty;
                    $cartItem->indent_quantity = $qty;
                    $cartItem->save();
                    return redirect('/cart')->with('status', "Qty diupdate. {$qty} item akan dipesan secara indent (DP 50%).");
                }

                // No indent_mode → ask user to choose
                return redirect('/cart')->with('indent_pending', [
                    'cart_item_id' => $cartItem->id,
                    'product_name' => $cartItem->product_name,
                    'requested_qty' => $qty,
                    'available_stock' => $stock,
                ]);
            }

            // Stock cukup → reset indent_quantity
            $cartItem->quantity = $qty;
            $cartItem->indent_quantity = 0;
            $cartItem->save();
            return redirect('/cart')->with('status', 'Qty diupdate.');
        }

        // Motor (no stock tracking)
        $cartItem->quantity = $qty;
        $cartItem->indent_quantity = 0;
        $cartItem->save();

        return redirect('/cart')->with('status', 'Qty diupdate.');
    }

    public function updateWithIndent(Request $request, CartItem $cartItem)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'indent_mode' => ['required', 'string', 'in:split,full'],
        ]);

        if ((int) $cartItem->cart->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $qty = (int) $validated['quantity'];
        $indentMode = $validated['indent_mode'];

        if ($cartItem->itemable_type !== PartVariant::class || !$cartItem->itemable) {
            return redirect('/cart')->withErrors(['indent' => 'Item ini tidak mendukung indent.']);
        }

        $variant = PartVariant::find($cartItem->itemable_id);
        $stock = $variant ? $variant->stock : 0;

        if ($qty <= $stock && $indentMode === 'split') {
            $cartItem->quantity = $qty;
            $cartItem->indent_quantity = 0;
            $cartItem->save();
            return redirect('/cart')->with('status', "Qty diupdate. Stok cukup, tidak perlu indent.");
        }

        if ($indentMode === 'split') {
            $cartItem->quantity = $stock;
            $cartItem->indent_quantity = $qty - $stock;
            $msg = "Split: {$stock} ready + " . ($cartItem->indent_quantity) . " indent (DP 50%).";
        } elseif ($indentMode === 'full') {
            $cartItem->quantity = $qty;
            $cartItem->indent_quantity = $qty;
            $msg = "Full Indent: {$qty} item akan dipesan indent (DP 50%).";
        } else {
            $cartItem->quantity = $stock;
            $cartItem->indent_quantity = 0;
            $msg = "Qty disesuaikan ke stok ({$stock}).";
        }

        $cartItem->save();
        return redirect('/cart')->with('status', $msg);
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        if ((int) $cartItem->cart->user_id !== (int) $request->user()->id) {
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
