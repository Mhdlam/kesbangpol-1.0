@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="py-12 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Hero Box -->
        <div class="bg-white dark:bg-slate-800 p-8 sm:p-12 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
            <div class="max-w-3xl space-y-4">
                <span class="inline-block px-3 py-1 bg-gov-50 dark:bg-slate-700 text-gov-600 dark:text-slate-200 rounded text-xs font-bold uppercase tracking-wider border border-gov-100 dark:border-slate-600">
                    Pemerintah Kota Palembang
                </span>
                
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                    Sistem Informasi Pengelolaan & Dokumentasi Ormas
                </h1>
                
                <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
                    Layanan portal resmi Badan Kesatuan Bangsa dan Politik (Kesbangpol) Kota Palembang untuk proses pendaftaran, pengelolaan, verifikasi, dan arsip 16 dokumen persyaratan Ormas berdasarkan Permendagri No. 57 Tahun 2017.
                </p>

                <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('user.dashboard') }}"
                           class="w-full sm:w-auto px-6 py-3 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors text-center">
                            Buka Dashboard {{ auth()->user()->isAdmin() ? 'Admin' : 'User' }}
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="w-full sm:w-auto px-6 py-3 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors text-center">
                            Daftar Akun Ormas Baru
                        </a>
                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto px-6 py-3 rounded-lg bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-50 transition-colors text-center">
                            Masuk ke Akun
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- 16 Requirement Info Cards -->
        <div class="mt-12 space-y-4">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                16 Berkas Persyaratan Pengajuan (Permendagri No. 57 Th 2017)
            </h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                    <span class="text-xs font-bold text-gov-600 dark:text-slate-300 block">1. Dokumen Pendirian</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Surat Permohonan, Akta Pendirian Notaris (AD/ART), AHU Kemenkumham, & Program Kerja Ormas.</p>
                </div>

                <div class="p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                    <span class="text-xs font-bold text-gov-600 dark:text-slate-300 block">2. Legalitas & Domisili</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400">SK Pengurus, Keterangan Domisili Lurah, Bukti Kepemilikan/Izin Tempat, & NPWP Ormas.</p>
                </div>

                <div class="p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                    <span class="text-xs font-bold text-gov-600 dark:text-slate-300 block">3. Surat Pernyataan</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Surat Bebas Sengketa (Download Word Kop Surat), Formulir Online Data Ormas, & Kesanggupan Lapor.</p>
                </div>

                <div class="p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2">
                    <span class="text-xs font-bold text-gov-600 dark:text-slate-300 block">4. Data Pengurus (KSB)</span>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Biodata, Pas Foto 4x6, serta KTP-El + No HP & WhatsApp Aktif Ketua, Sekretaris, & Bendahara.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
