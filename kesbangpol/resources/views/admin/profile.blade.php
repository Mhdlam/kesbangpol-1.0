@extends('layouts.app')

@section('title', 'Profile Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-user-shield text-gov-600"></i> Profile Administrator Kesbangpol
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
            Pengaturan profil petugas pemeriksa, informasi kedinasan, dan ubah password.
        </p>
    </div>

    <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
        <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Section 1: Identitas Admin -->
            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-gov-600 border-b border-slate-200 dark:border-slate-700 pb-2">
                    1. Informasi Profile Petugas
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Lengkap Petugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Jabatan Kedinasan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="position" value="{{ old('position', $user->position) }}" required placeholder="Contoh: Kepala Bidang Ormas"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                        @error('position')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Alamat Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            ID Login (Tepat 6 Digit Angka) <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="login_id" maxlength="6" pattern="[0-9]{6}" value="{{ old('login_id', $user->login_id) }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs font-mono tracking-widest">
                        @error('login_id')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Nomor WhatsApp Aktif <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone_wa" value="{{ old('phone_wa', $user->phone_wa) }}" required placeholder="081234567890"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                        @error('phone_wa')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Biodata Petugas
                        </label>
                        <input type="text" name="bio" value="{{ old('bio', $user->bio) }}" placeholder="Catatan tugas..."
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                    </div>
                </div>
            </div>

            <!-- Section 2: Pembaruan Password -->
            <div class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <h3 class="text-xs font-extrabold uppercase tracking-wider text-gov-600 border-b border-slate-200 dark:border-slate-700 pb-2">
                    2. Ubah Password Akun Admin (Opsional)
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Kosongkan form password di bawah ini jika tidak ingin mengubah password akun Anda.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Password Baru
                        </label>
                        <input type="password" name="password" placeholder="Minimal 6 karakter"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                            Konfirmasi Password Baru
                        </label>
                        <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                               class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-slate-200 dark:border-slate-700">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Simpan Perubahan Profile Admin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
