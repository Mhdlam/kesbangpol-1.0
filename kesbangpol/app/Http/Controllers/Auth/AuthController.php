<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('user.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id_or_email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login_id_or_email.required' => 'Email atau ID Login wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $input = trim($request->input('login_id_or_email'));
        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'login_id';

        $credentials = [
            $fieldType => $input,
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            \App\Services\ActivityLogger::log('login', "Berhasil masuk akun (" . strtoupper($user->role) . "): {$user->name} [ID: {$user->login_id}]", 'User', $user->id, $user);

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, Admin ' . $user->name);
            }

            return redirect()->intended(route('user.dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name);
        }

        throw ValidationException::withMessages([
            'login_id_or_email' => ['Email / ID Login atau password yang Anda masukkan salah.'],
        ]);
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('user.dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $messages = [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'login_id.required' => 'ID Login wajib diisi.',
            'login_id.digits' => 'ID Login harus tepat 6 digit angka.',
            'login_id.unique' => 'ID ini telah digunakan, coba pakai ID lain',
            'phone_wa.required' => 'Nomor HP WhatsApp aktif wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'login_id' => ['required', 'numeric', 'digits:6', 'unique:users,login_id'],
            'phone_wa' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'organization_name' => ['required', 'string', 'max:255'],
            'university' => ['nullable', 'string', 'max:255'],
        ], $messages);

        $role = 'user';

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'login_id' => $validated['login_id'],
            'phone_wa' => $validated['phone_wa'],
            'password' => Hash::make($validated['password']),
            'plain_password' => $validated['password'],
            'role' => $role,
            'university' => $request->input('university'),
        ]);


        if ($role === 'user') {
            Organization::create([
                'user_id' => $user->id,
                'name' => $request->input('organization_name', 'Organisasi ' . $user->name),
                'university' => $request->input('university'),
                'leader_name' => $user->name,
            ]);
        }

        Auth::login($user);
        \App\Services\ActivityLogger::log('register', "Pendaftaran akun Ormas baru: {$user->name} [ID: {$user->login_id}]", 'User', $user->id, $user);

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('success', 'Akun Admin berhasil dibuat!');
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Akun Ormas berhasil dibuat! Selamat datang di Sistem Informasi Kesbangpol.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \App\Services\ActivityLogger::log('logout', "Keluar akun dari sistem: {$user->name} [ID: {$user->login_id}]", 'User', $user->id, $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
