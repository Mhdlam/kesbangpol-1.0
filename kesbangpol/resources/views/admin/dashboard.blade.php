@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-gov-700 via-gov-600 to-slate-800 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-white text-xs font-semibold backdrop-blur-md mb-3 border border-white/20">
                    <i class="fa-solid fa-user-shield text-amber-400"></i> Panel Administrator Kesbangpol
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Dashboard Ringkasan Operasional</h1>
                <p class="text-slate-200 text-xs sm:text-sm mt-1 max-w-2xl">
                    Monitoring real-time verifikasi dokumen Ormas, manajemen pengajuan, dan tindakan administratif Kesbangpol Kota Palembang.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.submissions.index') }}" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs transition-all shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-inbox"></i> Berkas Masuk
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Action Shortcuts -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-amber-500"></i> Quick Action Admin
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- 1. Lihat Berkas Masuk (With Badge) -->
            <a href="{{ route('admin.submissions.index') }}" class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-gov-50 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700/60 transition-all flex flex-col items-center text-center group relative">
                <div class="absolute -top-2 -right-2 min-w-[24px] h-6 px-1.5 rounded-full bg-blue-600 text-white font-extrabold text-[11px] flex items-center justify-center shadow-md border-2 border-white dark:border-slate-900 z-10" title="{{ $quickActionCounts['berkas_masuk'] }} Berkas Masuk">
                    {{ $quickActionCounts['berkas_masuk'] }}
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 flex items-center justify-center font-bold mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-folder-open text-base"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Lihat Berkas Masuk</span>
            </a>

            <!-- 2. Mulai Pemeriksaan (With Realtime Badge) -->
            <a href="{{ route('admin.submissions.index', ['category' => 'action_needed']) }}" class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-gov-50 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700/60 transition-all flex flex-col items-center text-center group relative">
                <div class="absolute -top-2 -right-2 min-w-[24px] h-6 px-1.5 rounded-full bg-amber-500 text-slate-900 font-black text-[11px] flex items-center justify-center shadow-md border-2 border-white dark:border-slate-900 z-10" title="{{ $quickActionCounts['mulai_pemeriksaan'] }} Perlu Pemeriksaan">
                    {{ $quickActionCounts['mulai_pemeriksaan'] }}
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 flex items-center justify-center font-bold mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-[#1e5388] fa-magnifying-glass-doc text-base"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Mulai Pemeriksaan</span>
            </a>

            <!-- 3. Cari Ormas -->
            <a href="{{ route('admin.organizations') }}" class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-gov-50 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700/60 transition-all flex flex-col items-center text-center group">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 flex items-center justify-center font-bold mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-sitemap text-base"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Cari Ormas</span>
            </a>

            <!-- 4. Lihat Revisi (With Badge) -->
            <a href="{{ route('admin.submissions.index', ['category' => 'waiting_user']) }}" class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 hover:bg-gov-50 dark:hover:bg-slate-700/80 border border-slate-200 dark:border-slate-700/60 transition-all flex flex-col items-center text-center group relative">
                <div class="absolute -top-2 -right-2 min-w-[24px] h-6 px-1.5 rounded-full bg-orange-600 text-white font-extrabold text-[11px] flex items-center justify-center shadow-md border-2 border-white dark:border-slate-900 z-10" title="{{ $quickActionCounts['lihat_revisi'] }} Dalam Revisi">
                    {{ $quickActionCounts['lihat_revisi'] }}
                </div>
                <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-950/60 dark:text-orange-400 flex items-center justify-center font-bold mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-arrows-rotate text-base"></i>
                </div>
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Lihat Revisi</span>
            </a>
        </div>
    </div>

    <!-- "Pekerjaan Saya" Section -->
    <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-transparent dark:from-amber-950/30 border border-amber-300 dark:border-amber-800/60 rounded-2xl p-6 relative">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500 text-slate-900 font-black flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-list-check text-sm"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">Pekerjaan Saya</h2>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Tugas aktif Admin yang membutuhkan tindakan segera</p>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 font-extrabold text-[11px]">
                Prioritas Utama
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.submissions.index', ['category' => 'action_needed']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-slate-800 shadow-sm hover:border-amber-400 transition-all">
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $myTasks['awaiting_review'] }}</div>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1 block">Pengajuan Menunggu Pemeriksaan</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Perlu verifikasi awal</span>
            </a>

            <a href="{{ route('admin.submissions.index', ['category' => 'in_review']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-slate-800 shadow-sm hover:border-amber-400 transition-all">
                <div class="text-2xl font-black text-orange-600 dark:text-orange-400">{{ $myTasks['revisions_to_review'] }}</div>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1 block">Revisi dari Ormas Belum Ditinjau</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Dokumen telah diunggah ulang</span>
            </a>

            <a href="{{ route('admin.submissions.index', ['category' => 'action_needed']) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-slate-800 shadow-sm hover:border-amber-400 transition-all">
                <div class="text-2xl font-black text-sky-600 dark:text-sky-400">{{ $myTasks['docs_need_reverification'] }}</div>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1 block">Dokumen Perlu Diverifikasi Re-check</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Pending item review</span>
            </a>

            <a href="{{ route('admin.submissions.index', ['date_from' => date('Y-m-d')]) }}" class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-amber-200 dark:border-slate-800 shadow-sm hover:border-amber-400 transition-all">
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $myTasks['new_submissions_today'] }}</div>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 mt-1 block">Pengajuan Baru Masuk Hari Ini</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Diterima hari {{ date('d M Y') }}</span>
            </a>
        </div>
    </div>

    <!-- Real-Time Metrics Grid -->
    <div>
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
            <i class="fa-solid fa-chart-line text-blue-500"></i> Ringkasan Statistik Real-Time
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            
            <!-- Total User -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Akun User/Ormas</span>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($counts['total_users']) }}</div>
            </div>

            <!-- Total Org -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Organisasi Terdaftar</span>
                <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($counts['total_orgs']) }}</div>
            </div>

            <!-- Total Submissions -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Pengajuan Masuk</span>
                <div class="text-2xl font-extrabold text-gov-600 dark:text-sky-400 mt-1">{{ number_format($counts['total_submissions']) }}</div>
            </div>

            <!-- Belum Diperiksa -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-sky-200 dark:border-sky-900/60 shadow-sm">
                <span class="text-xs font-semibold text-sky-700 dark:text-sky-400">Pengajuan Baru (Belum Diperiksa)</span>
                <div class="text-2xl font-extrabold text-sky-600 dark:text-sky-400 mt-1">{{ number_format($counts['unreviewed']) }}</div>
            </div>

            <!-- Sedang Diperiksa -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-amber-200 dark:border-amber-900/60 shadow-sm">
                <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">Sedang Dalam Pemeriksaan</span>
                <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($counts['in_process']) }}</div>
            </div>

            <!-- Perlu Revisi -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-orange-200 dark:border-orange-900/60 shadow-sm">
                <span class="text-xs font-semibold text-orange-700 dark:text-orange-400">Membutuhkan Revisi</span>
                <div class="text-2xl font-extrabold text-orange-600 dark:text-orange-400 mt-1">{{ number_format($counts['revision_needed']) }}</div>
            </div>

            <!-- Diterima -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-emerald-200 dark:border-emerald-900/60 shadow-sm">
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Pengajuan Diterima</span>
                <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($counts['approved']) }}</div>
            </div>

            <!-- Ditolak -->
            <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-rose-200 dark:border-rose-900/60 shadow-sm">
                <span class="text-xs font-semibold text-rose-700 dark:text-rose-400">Pengajuan Ditolak</span>
                <div class="text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">{{ number_format($counts['rejected']) }}</div>
            </div>
        </div>
    </div>

    <!-- Time Period Submissions Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Masuk Hari Ini</span>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $counts['submitted_today'] }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ date('d F Y') }}</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Masuk Minggu Ini</span>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $counts['submitted_this_week'] }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Senin - Minggu Ini</span>
        </div>

        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Masuk Bulan Ini</span>
            <div class="text-3xl font-black text-slate-900 dark:text-white mt-2">{{ $counts['submitted_this_month'] }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Bulan {{ date('F Y') }}</span>
        </div>
    </div>

    <!-- District Breakdown Table (Sebaran Organisasi Berdasarkan Kecamatan) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
            <i class="fa-solid fa-map-location-dot text-amber-500"></i> Sebaran Organisasi Berdasarkan Kecamatan
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Nama Wilayah / Kecamatan</th>
                        <th class="px-4 py-3 text-right">Jumlah Ormas Terdaftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($districtStats as $ds)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-rose-500 text-xs"></i>
                                {{ $ds->district ?? 'Kecamatan Tidak Diisi' }}
                            </td>
                            <td class="px-4 py-3 font-extrabold text-gov-600 dark:text-sky-400 text-right">
                                {{ number_format($ds->count) }} Ormas
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-slate-400">
                                Belum ada data sebaran wilayah terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Priority Queue Submissions List -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Antrean Pengajuan Butuh Tindakan</h3>
                <p class="text-xs text-slate-500">10 pengajuan teratas yang memerlukan pemeriksaan Admin</p>
            </div>
            <a href="{{ route('admin.submissions.index') }}" class="text-xs font-bold text-gov-600 hover:text-gov-700 dark:text-sky-400 flex items-center gap-1">
                Semua Berkas <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">No. Pengajuan</th>
                        <th class="px-6 py-3.5">Nama Ormas</th>
                        <th class="px-6 py-3.5">Pemohon</th>
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5">Admin Pemeriksa</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($prioritySubmissions as $submission)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono font-bold text-slate-900 dark:text-white">
                                #{{ $submission->tracking_code }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $submission->organization->name ?? ($submission->user->name ?? '-') }}
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ $submission->user->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                {{ $submission->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                    {{ $submission->status->name ?? 'Belum Diperiksa' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                {{ $submission->assignedAdmin->name ?? 'Belum Ditugaskan' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.submissions.show', $submission->id) }}" class="px-3 py-1.5 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs transition-colors shadow-sm inline-flex items-center gap-1.5">
                                    <i class="fa-solid fa-[#1e5388] fa-magnifying-glass"></i> Periksa
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-circle-check text-4xl text-emerald-400 mb-2 block"></i>
                                Tidak ada antrean pengajuan yang menunggu pemeriksaan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
