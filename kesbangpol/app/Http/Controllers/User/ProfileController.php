<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $org = $user->organization;

        return view('user.profile', compact('user', 'org'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255'],
            'university' => ['nullable', 'string', 'max:255'],
            'phone_wa' => ['required', 'string', 'max:20'],
            'bio' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'login_id' => ['required', 'numeric', 'digits:6', Rule::unique('users', 'login_id')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'organization_name.required' => 'Nama organisasi wajib diisi.',
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
            'university' => $validated['university'],
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

        $org = $user->organization ?? new Organization(['user_id' => $user->id]);
        $org->name = $validated['organization_name'];
        $org->university = $validated['university'];
        $org->save();

        return redirect()->route('user.profile')->with('success', 'Profil Anda berhasil diperbarui!');
    }
}
