<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('buyer.addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('buyer.addresses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:16'],
            'notes' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            if ((bool) ($validated['is_default'] ?? false)) {
                Address::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
            }

            Address::create(array_merge($validated, [
                'user_id' => $request->user()->id,
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]));

            return redirect('/account/addresses')->with('status', 'Alamat berhasil ditambahkan.');
        });
    }

    public function edit(Address $address, Request $request)
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        return view('buyer.addresses.edit', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:32'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:16'],
            'notes' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $address, $validated) {
            if ((bool) ($validated['is_default'] ?? false)) {
                Address::query()->where('user_id', $request->user()->id)->update(['is_default' => false]);
            }

            $address->fill(array_merge($validated, [
                'is_default' => (bool) ($validated['is_default'] ?? false),
            ]))->save();

            return redirect('/account/addresses')->with('status', 'Alamat berhasil diupdate.');
        });
    }

    public function destroy(Request $request, Address $address)
    {
        if ((int) $address->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $address->delete();

        return redirect('/account/addresses')->with('status', 'Alamat dihapus.');
    }
}

