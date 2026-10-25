@extends('layouts.app')

@section('title', 'Data Organisasi')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-sitemap text-red-600"></i> Data Organisasi Masyarakat (Ormas)
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Daftar Organisasi Masyarakat dan Mahasiswa terdaftar di Kesbangpol Palembang.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-xs font-bold text-red-600 dark:text-red-400 hover:underline">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-700 dark:text-slate-300 uppercase tracking-wider font-extrabold border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="px-6 py-4">Nama Ormas</th>
                        <th class="px-6 py-4">Universitas</th>
                        <th class="px-6 py-4">Pengurus Inti (KSB)</th>
                        <th class="px-6 py-4">Kategori Keagamaan</th>
                        <th class="px-6 py-4">Alamat Sekretariat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @foreach($organizations as $org)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-700/40 transition-colors">
                            <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                                {{ $org->name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $org->university ?? '-' }}
                            </td>
                            <td class="px-6 py-4 space-y-0.5">
                                <span class="block">Ketua: <strong>{{ $org->leader_name ?? '-' }}</strong></span>
                                <span class="block text-[11px] text-slate-500">Sek: {{ $org->secretary_name ?? '-' }} | Bend: {{ $org->treasurer_name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $org->is_religious ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200 border border-amber-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300' }}">
                                    {{ $org->is_religious ? 'Ormas Keagamaan' : 'Umum' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate">
                                {{ $org->address ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            {{ $organizations->links() }}
        </div>
    </div>
</div>
@endsection
