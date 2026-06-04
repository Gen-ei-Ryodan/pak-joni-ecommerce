<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Motor;
use App\Models\MotorCategory;
use App\Models\PartCategory;
use Illuminate\Http\Request;

class MotorController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $motors = Motor::query()
            ->with(['brand', 'category'])
            ->where('status', 'active')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%'.$q.'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('buyer.motors.index', compact('motors', 'q'));
    }

    public function show(Motor $motor, Request $request)
    {
        $motor->load([
            'brand',
            'category',
            'images',
            'colors',
            'specifications',
            'images360',
        ]);

        $tab = $request->query('tab', 'detail');
        $selectedPartGroup = $request->query('part_group');

        $partGroups = PartCategory::query()
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        $partsQuery = $motor->parts()
            ->with(['category', 'defaultVariant', 'motors.brand'])
            ->where('status', 'active');

        if ($selectedPartGroup) {
            $partsQuery->whereHas('category', fn($q) => $q->where('group', $selectedPartGroup));
        }

        $parts = $partsQuery
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $partsGrouped = $motor->parts()
            ->with(['category', 'defaultVariant'])
            ->where('status', 'active')
            ->get()
            ->groupBy(fn($p) => $p->category?->group ?? 'Lainnya');

        $relatedMotors = Motor::query()
            ->with(['brand'])
            ->where('status', 'active')
            ->where('id', '!=', $motor->id)
            ->where(function($q) use ($motor) {
                $q->where('brand_id', $motor->brand_id)
                  ->orWhere('category_id', $motor->category_id);
            })
            ->take(4)
            ->get();

        $specGroups = $motor->specifications->groupBy('group');

        return view('buyer.motors.show', compact(
            'motor', 'partGroups', 'parts', 'partsGrouped',
            'relatedMotors', 'specGroups', 'tab', 'selectedPartGroup'
        ));
    }
}
