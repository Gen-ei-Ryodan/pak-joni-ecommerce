<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Motor;
use App\Models\PartCategory;
use Illuminate\Http\Request;

class MotorController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $motors = Motor::query()
            ->where('status', 'active')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.motors.index', compact('motors', 'q'));
    }

    public function show(Motor $motor)
    {
        $motor->load(['images']);

        $categories = PartCategory::query()
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group');

        $parts = $motor->parts()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->get()
            ->groupBy(fn ($p) => $p->category?->group ?? 'part');

        return view('buyer.motors.show', compact('motor', 'categories', 'parts'));
    }
}
