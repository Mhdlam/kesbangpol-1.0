@extends('layouts.app')

@section('title', 'Login Akun')

@section('content')
<div class="min-h-[calc(100vh-12rem)] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 bg-white dark:bg-slate-800 p-8 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="text-center space-y-2">
            <div class="w-12 h-12 bg-gov-50 dark:bg-slate-700 text-gov-600 dark:text-slate-200 rounded-lg flex items-center justify-center mx-auto text-xl font-bold border border-gov-100 dark:border-slate-600">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white">
                Masuk ke Sistem Kesbangpol
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Sistem Pengelolaan & Dokumentasi Ormas Palembang
            </p>
            
            <!-- Requirement Notice -->
            <div class="p-3 bg-gov-50 dark:bg-slate-900 border border-gov-100 dark:border-slate-700 rounded-lg text-gov-700 dark:text-slate-300 text-xs font-medium text-left flex items-start gap-2 mt-3">
                <i class="fa-solid fa-circle-info text-gov-600 text-sm mt-0.5 shrink-0"></i>
                <span>User atau Admin bisa login menggunakan Email atau ID Login</span>
            </div>
        </div>

        <form class="space-y-4" action="{{ route('login.store') }}" method="POST"
              x-data="{
                  rememberChecked: false,
                  showRememberModal: false,
                  confirmRemember() {
                      this.rememberChecked = true;
                      this.showRememberModal = false;
                  },
                  cancelRemember() {
                      this.rememberChecked = false;
                      this.showRememberModal = false;
                  }
              }">
            @csrf
            <div>
                <label for="login_id_or_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Email ATAU ID Login (6 Digit) <span class="text-red-500">*</span>
                </label>
                <input id="login_id_or_email" name="login_id_or_email" type="text" value="{{ old('login_id_or_email') }}" required autofocus
                       placeholder="Contoh: user@mahasiswa.ac.id atau 123456"
                       class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                @error('login_id_or_email')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showPassword: false }">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                    Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                           placeholder="Masukkan password akun Anda"
                           class="w-full px-3.5 py-2.5 pr-10 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-slate-900 dark:text-white text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent">
                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none"
                            title="Tampilkan / Sembunyikan Password">
                        <i class="fa-solid" :class="showPassword ? 'fa-eye-slash text-gov-600 dark:text-amber-400' : 'fa-eye'"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" value="1"
                           x-model="rememberChecked"
                           @click="if (!$el.checked) { rememberChecked = false; } else { $event.preventDefault(); showRememberModal = true; }"
                           class="w-4 h-4 rounded text-gov-600 border-slate-300 dark:border-slate-700 focus:ring-gov-500">
                    <span class="text-slate-600 dark:text-slate-400 font-medium">Ingat Saya</span>
                </label>
            </div>

            <div>
                <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Masuk Sekarang
                </button>
            </div>

            <!-- Remember Me Confirmation Modal Popup -->
            <div x-show="showRememberModal" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                 @keydown.escape.window="cancelRemember()">
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 max-w-sm w-full p-6 text-center space-y-4"
                     @click.away="cancelRemember()">
                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Konfirmasi Mode "Ingat Saya"</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                            Apakah anda yakin? mengaktifkan mode Ingat Saya secara berkala dapat membuat anda lupa akan password anda ketika login.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="cancelRemember()"
                                class="flex-1 py-2.5 px-3 rounded-lg border border-slate-300 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="button" @click="confirmRemember()"
                                class="flex-1 py-2.5 px-3 rounded-lg bg-gov-600 hover:bg-gov-700 text-white text-xs font-bold shadow-sm transition-colors">
                            Ya, Saya Yakin
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div class="text-center pt-3 border-t border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Belum memiliki akun Ormas? 
                <a href="{{ route('register') }}" class="font-bold text-gov-600 dark:text-slate-300 hover:underline ms-1">
                    Daftar Akun Baru
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
