@extends('layouts.app')

@section('title', 'Aktivitas')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-user-shield text-gov-600 dark:text-gov-500"></i> Audit Trail Aktivitas Admin
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Rekam jejak seluruh aktivitas pengoperasian Petugas Admin Kesbangpol lengkap dengan timestamp dan identitas tindakan.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-gov-600 dark:text-slate-300 hover:underline">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Info Auto Clean 30 Days -->
    <div class="bg-blue-50/80 dark:bg-slate-800/80 border border-blue-200 dark:border-slate-700 rounded-xl p-3.5 flex items-center gap-3 text-xs text-blue-900 dark:text-blue-200 shadow-sm">
        <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-base flex-shrink-0"></i>
        <span><strong>Informasi Sistem:</strong> Semua history Aktivitas Admin akan terhapus otomatis secara berkala setiap 30 hari.</span>
    </div>

    <!-- Filter Form -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.activity_logs') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aksi / nama Admin / email / deskripsi aktivitas..."
                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-gov-500">
            </div>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
            <button type="submit" class="px-5 py-2 rounded-xl bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs transition-colors shadow-sm">
                Filter Aktivitas
            </button>
            @if(request()->hasAny(['search', 'date']))
                <a href="{{ route('admin.activity_logs') }}" class="px-4 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold text-center flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Waktu (Timestamp)</th>
                        <th class="px-4 py-3.5">Petugas Admin</th>
                        <th class="px-4 py-3.5">Aksi (Action)</th>
                        <th class="px-6 py-3.5">Deskripsi Aktivitas</th>
                        <th class="px-4 py-3.5">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3.5 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }} WIB
                            </td>
                            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                                <span class="block">{{ $log->user->name ?? 'Admin Kesbangpol' }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-extrabold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300 mt-0.5">
                                    ADMIN
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-1 rounded font-mono font-extrabold text-[10px] uppercase border {{ str_contains($log->action, 'delete') || str_contains($log->action, 'reject') ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:border-rose-900' : (str_contains($log->action, 'approve') || str_contains($log->action, 'login') ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:border-emerald-900' : 'bg-slate-100 text-slate-800 border-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:border-slate-700') }}">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 dark:text-slate-300 leading-relaxed">
                                {{ $log->description }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                                {{ $log->ip_address ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-user-shield text-3xl text-slate-300 dark:text-slate-700 mb-2 block"></i>
                                Belum ada catatan aktivitas Admin ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
