<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Part;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = Wishlist::query()
            ->with(['part.category', 'part.defaultVariant'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('id')
            ->paginate(12);

        return view('buyer.wishlist.index', compact('items'));
    }

    public function toggle(Request $request, Part $part)
    {
        $userId = $request->user()->id;

        $existing = Wishlist::query()->where('user_id', $userId)->where('part_id', $part->id)->first();

        if ($existing) {
            $existing->delete();
            return back()->with('status', 'Wishlist dihapus.');
        }

        Wishlist::create([
            'user_id' => $userId,
            'part_id' => $part->id,
        ]);

        return back()->with('status', 'Wishlist ditambahkan.');
    }
}

