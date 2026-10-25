@extends('layouts.app')

@section('title', 'Semua Berkas - History Pengajuan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-folder-open text-red-600"></i> Semua Berkas
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Riwayat seluruh pengajuan pendaftaran dan status berkas milik Anda.
            </p>
        </div>

        <div class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-amber-500"></i>
            <span>History otomatis dibersihkan setelah <strong class="text-slate-800 dark:text-slate-200">30 Hari</strong></span>
        </div>
    </div>

    @if($submissions->isEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 flex items-center justify-center mx-auto text-3xl">
                <i class="fa-solid fa-folder-minus"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Belum Ada Riwayat Pengajuan</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                Anda belum pernah mengirimkan berkas pengajuan pendaftaran Ormas ke Kesbangpol.
            </p>
            <a href="{{ route('user.dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-xs shadow-md">
                <i class="fa-solid fa-cloud-arrow-up"></i> Ke Dashboard Pendaftaran
            </a>
        </div>
    @else
        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                    <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 uppercase tracking-wider font-extrabold border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4">Kode Tracking</th>
                            <th class="px-6 py-4">Nama Pengajuan</th>
                            <th class="px-6 py-4">Tanggal Pengajuan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($submissions as $sub)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                                <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">
                                    {{ $sub->tracking_code }}
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $sub->title }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ $sub->created_at->translatedFormat('d F Y, H:i') }} WIB
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200 border border-amber-300 dark:border-amber-800">
                                        {{ $sub->status->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('user.history.detail', $sub->id) }}" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300 font-bold hover:bg-red-100 transition-colors">
                                        <i class="fa-solid fa-circle-info me-1"></i> Detail Berkas
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
