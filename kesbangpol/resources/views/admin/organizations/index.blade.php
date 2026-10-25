@extends('layouts.app')

@section('title', 'Data Organisasi Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Title & Status Tabs (Requirement K & X) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-sitemap text-gov-600 dark:text-gov-500"></i> Database Utama Organisasi (Ormas)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Database terpusat seluruh Ormas yang terdaftar di Kesbangpol Kota Palembang.
            </p>
        </div>
    </div>

    <!-- Status Archiving Filter Tabs (Section X) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-2 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap gap-1 text-xs font-bold">
        <a href="{{ route('admin.organizations', ['status' => 'active']) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ request('status') === 'active' || !request('status') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-circle-check"></i> Aktif ({{ $counts['active'] }})
        </a>
        <a href="{{ route('admin.organizations', ['status' => 'inactive']) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ request('status') === 'inactive' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-circle-pause"></i> Tidak Aktif ({{ $counts['inactive'] }})
        </a>
        <a href="{{ route('admin.organizations', ['status' => 'archived']) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ request('status') === 'archived' ? 'bg-slate-700 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-box-archive"></i> Diarsipkan ({{ $counts['archived'] }})
        </a>
    </div>

    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 border border-slate-200 dark:border-slate-800 shadow-sm">
        <form method="GET" action="{{ route('admin.organizations') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="hidden" name="status" value="{{ request('status', 'active') }}">
            <div class="relative flex-grow">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari Nama Ormas / Singkatan / No. Registrasi / Ketua / Alamat..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
            </div>
            <button type="submit" class="px-5 py-2 rounded-xl bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors">
                Cari Organisasi
            </button>
        </form>
    </div>

    <!-- Organizations Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3.5">Nama Organisasi</th>
                        <th class="px-4 py-3.5">Ketua Umum</th>
                        <th class="px-4 py-3.5">User Akun</th>
                        <th class="px-4 py-3.5">Jumlah Pengajuan</th>
                        <th class="px-4 py-3.5">Status Ormas</th>
                        <th class="px-4 py-3.5 text-right">Detail & Workspace</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($organizations as $org)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-extrabold text-slate-900 dark:text-white">
                                {{ $org->name }}
                                @if($org->abbreviation)
                                    <span class="text-slate-400 font-normal">({{ $org->abbreviation }})</span>
                                @endif
                                <span class="block text-[11px] font-normal text-slate-500">{{ $org->address ?? 'Alamat belum diisi' }}</span>
                            </td>
                            <td class="px-4 py-4 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $org->leader_name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-slate-600 dark:text-slate-400">
                                {{ $org->user->name ?? '-' }}
                                <span class="block text-[10px] text-slate-400">{{ $org->user->login_id ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-4 font-bold text-slate-800 dark:text-slate-200">
                                {{ $org->submissions->count() }} Submissions
                            </td>
                            <td class="px-4 py-4">
                                @if($org->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Aktif</span>
                                @elseif($org->status === 'inactive')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Tidak Aktif</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300">Diarsipkan</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right">
                                <a href="{{ route('admin.organizations.show', $org->id) }}" class="px-3 py-1.5 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs transition-colors shadow-sm inline-flex items-center gap-1">
                                    <i class="fa-solid fa-folder-open"></i> Central Detail Page
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-sitemap text-4xl text-slate-300 dark:text-slate-700 mb-2 block"></i>
                                Belum ada data organisasi terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $organizations->links() }}
        </div>
    </div>

</div>
@endsection
