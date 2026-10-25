@extends('layouts.app')

@section('title', 'Berkas Masuk')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-inbox text-gov-600 dark:text-gov-500"></i> Berkas Masuk Submission
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                Daftar seluruh berkas pengajuan pendaftaran Ormas yang telah dikirimkan oleh pemohon.
            </p>
        </div>
    </div>

    <!-- Work Priority Categories Navigation Tabs (Section V) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-2 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap gap-1 text-xs font-bold">
        <a href="{{ route('admin.submissions.index', array_merge(request()->query(), ['category' => 'all'])) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $category === 'all' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-list-ul"></i> Semua Berkas
        </a>
        <a href="{{ route('admin.submissions.index', array_merge(request()->query(), ['category' => 'action_needed'])) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $category === 'action_needed' ? 'bg-amber-500 text-slate-900 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-triangle-exclamation"></i> Perlu Tindakan
        </a>
        <a href="{{ route('admin.submissions.index', array_merge(request()->query(), ['category' => 'waiting_user'])) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $category === 'waiting_user' ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-clock-rotate-left"></i> Menunggu User (Revisi)
        </a>
        <a href="{{ route('admin.submissions.index', array_merge(request()->query(), ['category' => 'in_review'])) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $category === 'in_review' ? 'bg-sky-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-spinner"></i> Sedang Diperiksa
        </a>
        <a href="{{ route('admin.submissions.index', array_merge(request()->query(), ['category' => 'completed'])) }}"
           class="px-4 py-2.5 rounded-xl transition-all flex items-center gap-2 {{ $category === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-circle-check"></i> Selesai
        </a>
    </div>

    <!-- Filter & Search Bar (Section B & Z) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.submissions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <input type="hidden" name="category" value="{{ $category }}">

            <!-- Unified Search Input -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Global</label>
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nama Ormas / No. Pengajuan / User / Email / Ketua..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
                </div>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Status</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st->code }}" {{ request('status') === $st->code ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- District Filter (Kecamatan) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kecamatan</label>
                <input type="text" name="district" value="{{ request('district') }}" placeholder="Contoh: Sukarami..."
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
            </div>

            <!-- Assigned Admin Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Admin Pemeriksa</label>
                <select name="assigned_admin_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
                    <option value="">Semua Admin</option>
                    <option value="unassigned" {{ request('assigned_admin_id') === 'unassigned' ? 'selected' : '' }}>Belum Ditugaskan</option>
                    @foreach($admins as $adm)
                        <option value="{{ $adm->id }}" {{ request('assigned_admin_id') == $adm->id ? 'selected' : '' }}>
                            {{ $adm->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sorting Filter (Section B) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Urutkan (Sorting)</label>
                <select name="sort" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
                    <option value="terbaru" {{ $sort === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                    <option value="terlama" {{ $sort === 'terlama' ? 'selected' : '' }}>Terlama</option>
                    <option value="belum_diperiksa" {{ $sort === 'belum_diperiksa' ? 'selected' : '' }}>Belum diperiksa</option>
                    <option value="terakhir_diperbarui" {{ $sort === 'terakhir_diperbarui' ? 'selected' : '' }}>Terakhir diperbarui</option>
                </select>
            </div>

            <div class="lg:col-span-6 flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('admin.submissions.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 transition-colors">
                    Reset Filter
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Terapkan Filter & Urutan
                </button>
            </div>
        </form>
    </div>

    <!-- Submissions Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5">Nomor Pengajuan</th>
                        <th class="px-6 py-3.5">Nama Ormas</th>
                        <th class="px-4 py-3.5">Nama User</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Kelengkapan</th>
                        <th class="px-4 py-3.5">Diperbarui</th>
                        <th class="px-4 py-3.5">Admin Pemeriksa</th>
                        <th class="px-4 py-3.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($submissions as $s)
                        @php
                            $reqCheck = \App\Services\RequirementChecker::checkUserStatus($s->user, $s);
                            $pct = min(100, $reqCheck['percentage']);
                        @endphp
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors {{ $s->status && $s->status->code === 'submitted' ? 'bg-sky-50/40 dark:bg-sky-950/20' : '' }}">
                            <td class="px-4 py-4 font-mono font-bold text-slate-900 dark:text-white">
                                #{{ $s->tracking_code }}
                                @if($s->revision_count > 0)
                                    <span class="block text-[10px] text-orange-600 dark:text-orange-400 font-extrabold">Revisi #{{ $s->revision_count }}</span>
                                @endif
                            </td>
                                <td class="px-6 py-4 font-extrabold text-slate-800 dark:text-slate-200">
                                    {{ $s->organization->name ?? ($s->user->name ?? '-') }}
                                    @if($s->organization && $s->organization->abbreviation)
                                        <span class="text-slate-400 font-normal">({{ $s->organization->abbreviation }})</span>
                                    @endif
                                    @if($s->district)
                                        <span class="block text-[11px] font-normal text-slate-500">Kec. {{ $s->district }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-slate-600 dark:text-slate-400">
                                    {{ $s->user->name ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-slate-500">
                                    {{ $s->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4">
                                    @php
                                        $badgeBg = 'bg-slate-100 text-slate-700';
                                        if ($s->status) {
                                            if ($s->status->code === 'submitted') $badgeBg = 'bg-sky-100 text-sky-800 dark:bg-sky-950 border-sky-300';
                                            elseif ($s->status->code === 'in_process') $badgeBg = 'bg-amber-100 text-amber-800 dark:bg-amber-950 border-amber-300';
                                            elseif ($s->status->code === 'revision_needed') $badgeBg = 'bg-orange-100 text-orange-800 dark:bg-orange-950 border-orange-300';
                                            elseif ($s->status->code === 'approved') $badgeBg = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 border-emerald-300';
                                            elseif ($s->status->code === 'rejected') $badgeBg = 'bg-rose-100 text-rose-800 dark:bg-rose-950 border-rose-300';
                                        }
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold border {{ $badgeBg }}">
                                        {{ $s->status->name ?? 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                            <div class="bg-gov-600 h-full rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="font-extrabold text-[11px]">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-slate-500">
                                    {{ $s->updated_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-4 text-slate-600 dark:text-slate-400 font-semibold">
                                    {{ $s->assignedAdmin->name ?? 'Belum Ditugaskan' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('admin.submissions.show', $s->id) }}" class="px-3 py-1.5 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs transition-colors shadow-sm inline-flex items-center gap-1.5">
                                        <i class="fa-solid fa-[#1e5388] fa-magnifying-glass"></i> Periksa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-16 text-center text-slate-500">
                                    <i class="fa-solid fa-inbox text-5xl text-slate-300 dark:text-slate-700 mb-3 block"></i>
                                    <span class="font-bold text-sm block">Belum ada berkas masuk.</span>
                                    <span class="text-xs">Tidak ditemukan data submission yang sesuai dengan kriteria filter.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $submissions->links() }}
            </div>
        </div>

</div>
@endsection
