@extends('layouts.app')

@section('title', 'Registrasi Akun Ormas')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full space-y-6 bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-gov-50 dark:bg-slate-700 text-gov-600 dark:text-slate-200 rounded-lg flex items-center justify-center mx-auto text-xl font-bold border border-gov-100 dark:border-slate-600">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
                Pendaftaran Akun Ormas Baru
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Sistem Pengelolaan & Dokumentasi Kesbangpol Kota Palembang
            </p>

            <!-- Requirement Notice -->
            <div class="p-3 bg-gov-50 dark:bg-slate-900 border border-gov-100 dark:border-slate-700 rounded-lg text-gov-700 dark:text-slate-300 text-xs font-medium text-left flex items-start gap-2 mt-3">
                <i class="fa-solid fa-circle-info text-gov-600 text-sm mt-0.5 shrink-0"></i>
                <div>
                    <span>User atau Admin bisa login menggunakan Email atau ID Login</span>
                    <span class="block text-[11px] text-slate-500 mt-0.5">ID Login harus tepat 6 digit angka dan bersifat unik.</span>
                </div>
            </div>
        </div>

        <form class="space-y-4" action="{{ route('register.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Lengkap / Ketua <span class="text-red-500">*</span>
                    </label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           placeholder="Contoh: Ahmad Hidayat"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    @error('name')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Alamat Email <span class="text-red-500">*</span>
                    </label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           placeholder="ormas@mahasiswa.ac.id"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Login ID 6 digits -->
                <div>
                    <label for="login_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Buat ID Login (Tepat 6 Digit) <span class="text-red-500">*</span>
                    </label>
                    <input id="login_id" name="login_id" type="text" maxlength="6" pattern="[0-9]{6}" value="{{ old('login_id') }}" required
                           placeholder="Contoh: 654321"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs font-mono tracking-widest placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    @error('login_id')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- WhatsApp Phone Number -->
                <div>
                    <label for="phone_wa" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        No. HP WhatsApp Aktif <span class="text-red-500">*</span>
                    </label>
                    <input id="phone_wa" name="phone_wa" type="text" value="{{ old('phone_wa') }}" required
                           placeholder="Contoh: 081234567890"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    @error('phone_wa')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="organization_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Nama Organisasi <span class="text-red-500">*</span>
                    </label>
                    <input id="organization_name" name="organization_name" type="text" value="{{ old('organization_name') }}" required
                           placeholder="Contoh: HMI Cabang Palembang"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    @error('organization_name')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="university" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Perguruan Tinggi / Universitas
                    </label>
                    <input id="university" name="university" type="text" value="{{ old('university') }}"
                           placeholder="Contoh: Universitas Sriwijaya"
                           class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    @error('university')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div x-data="{ showPass: false }">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password" name="password" :type="showPass ? 'text' : 'password'" required
                               placeholder="Minimal 6 karakter"
                               class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                        <button type="button" @click="showPass = !showPass"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none"
                                title="Tampilkan / Sembunyikan Password">
                            <i class="fa-solid" :class="showPass ? 'fa-eye-slash text-gov-600 dark:text-amber-400' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ showConfirm: false }">
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'" required
                               placeholder="Ulangi password"
                               class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                        <button type="button" @click="showConfirm = !showConfirm"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none"
                                title="Tampilkan / Sembunyikan Konfirmasi Password">
                            <i class="fa-solid" :class="showConfirm ? 'fa-eye-slash text-gov-600 dark:text-amber-400' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Mendaftar Akun Ormas
                </button>
            </div>
        </form>

        <div class="text-center pt-3 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="font-bold text-gov-600 dark:text-slate-300 hover:underline ms-1">
                    Masuk Sekarang
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
