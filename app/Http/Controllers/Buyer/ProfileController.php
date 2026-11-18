<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('buyer.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'ktp_no' => ['required', 'string', 'max:30'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'ktp_no.required' => 'Nomor KTP wajib diisi untuk verifikasi transaksi & SPK.',
        ]);

        $user->update($validated);

        return redirect()->route('buyer.profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }
}
