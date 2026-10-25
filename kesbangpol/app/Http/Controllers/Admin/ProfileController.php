<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'phone_wa' => ['required', 'string', 'max:20'],
            'bio' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'login_id' => ['required', 'numeric', 'digits:6', Rule::unique('users', 'login_id')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'position.required' => 'Jabatan wajib diisi.',
            'phone_wa.required' => 'Nomor WhatsApp aktif wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'login_id.required' => 'ID Login wajib diisi.',
            'login_id.digits' => 'ID Login harus tepat 6 digit angka.',
            'login_id.unique' => 'ID ini telah digunakan, coba pakai ID lain',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'position' => $validated['position'],
            'phone_wa' => $validated['phone_wa'],
            'bio' => $validated['bio'],
            'email' => strtolower($validated['email']),
            'login_id' => $validated['login_id'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = \Illuminate\Support\Facades\Hash::make($request->input('password'));
            $updateData['plain_password'] = $request->input('password');
        }

        $user->update($updateData);

        return redirect()->route('admin.profile')->with('success', 'Profil Admin berhasil diperbarui!');
    }
}
