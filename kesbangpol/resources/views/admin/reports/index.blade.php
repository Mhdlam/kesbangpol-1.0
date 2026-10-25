@extends('layouts.app')

@section('title', 'Laporan Administratif Kesbangpol')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-chart-pie text-emerald-600 dark:text-emerald-500"></i> Laporan & Monitoring Administratif
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Laporan rekapitulasi jumlah Ormas, status pengajuan, sebaran wilayah, dan efisiensi waktu pemeriksaan.
            </p>
        </div>
    </div>

    <!-- Period Filter Buttons (Requirement T) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm space-y-3">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Filter Periode Laporan:</span>
        <div class="flex flex-wrap gap-2 text-xs font-bold">
            <a href="{{ route('admin.reports', ['period' => 'today']) }}" class="px-4 py-2 rounded-xl transition-all {{ $period === 'today' ? 'bg-gov-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                Hari Ini
            </a>
            <a href="{{ route('admin.reports', ['period' => 'this_week']) }}" class="px-4 py-2 rounded-xl transition-all {{ $period === 'this_week' ? 'bg-gov-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                Minggu Ini
            </a>
            <a href="{{ route('admin.reports', ['period' => 'this_month']) }}" class="px-4 py-2 rounded-xl transition-all {{ $period === 'this_month' ? 'bg-gov-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                Bulan Ini
            </a>
        </div>
    </div>

    <!-- Stats Overview Grid (Requirement T) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Ormas Terdaftar</span>
            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($stats['total_orgs']) }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-[11px] font-semibold text-slate-500 block">Total Pengajuan Masuk</span>
            <div class="text-2xl font-black text-gov-600 dark:text-sky-400 mt-1">{{ number_format($stats['total_submissions']) }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/60 shadow-sm">
            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 block">Pengajuan Diterima</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($stats['approved']) }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-orange-200 dark:border-orange-900/60 shadow-sm">
            <span class="text-[11px] font-semibold text-orange-700 dark:text-orange-400 block">Pengajuan Revisi</span>
            <div class="text-2xl font-black text-orange-600 mt-1">{{ number_format($stats['revision_needed']) }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-rose-200 dark:border-rose-900/60 shadow-sm">
            <span class="text-[11px] font-semibold text-rose-700 dark:text-rose-400 block">Pengajuan Ditolak</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($stats['rejected']) }}</div>
        </div>

        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-amber-200 dark:border-amber-900/60 shadow-sm">
            <span class="text-[11px] font-semibold text-amber-700 dark:text-amber-400 block">Menunggu Pemeriksaan</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($stats['pending_review']) }}</div>
        </div>
    </div>

    <!-- Verification Stage Processing Time Monitoring (Requirement U) -->
    <div class="bg-gradient-to-r from-gov-700 to-slate-800 rounded-2xl p-6 text-white shadow-md flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <span class="text-xs font-bold text-amber-400 uppercase tracking-wider block">Requirement U — Verification Duration Monitoring</span>
            <h3 class="text-lg font-extrabold mt-1">Rata-Rata Durasi Waktu Pemeriksaan Berkas Kesbangpol</h3>
            <p class="text-xs text-slate-300 mt-0.5">Dihitung otomatis dari timestamp saat pengajuan dikirim hingga penetapan status diterima/ditolak.</p>
        </div>
        <div class="text-center md:text-right bg-white/10 backdrop-blur-md p-4 rounded-xl border border-white/20">
            <div class="text-3xl font-black text-amber-400">{{ $avgProcessingHours }} Jam</div>
            <span class="text-[11px] text-slate-200 font-bold block mt-0.5">Rerata Penyelesaian</span>
        </div>
    </div>

    <!-- District Breakdown Table (Requirement T) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Sebaran Organisasi Berdasarkan Kecamatan</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-4 py-3">Nama Wilayah / Kecamatan</th>
                        <th class="px-4 py-3">Jumlah Ormas Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($districtStats as $ds)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $ds->district ?? 'Kecamatan Tidak Diisi' }}</td>
                            <td class="px-4 py-3 font-extrabold text-gov-600 dark:text-sky-400">{{ $ds->count }} Ormas</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-4 text-center text-slate-400">Belum ada sebaran wilayah recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
