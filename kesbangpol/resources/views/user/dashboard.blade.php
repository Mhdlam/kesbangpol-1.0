@extends('layouts.app')

@section('title', $is_revision_mode ? 'Mode Revisi Pengajuan - Dashboard User' : 'Dashboard User')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6" x-data="{ 
    uploadModal: false, 
    activeDocKey: '', 
    activeDocTitle: '', 
    activeDocNote: '',
    confirmModal: false
}">

    <!-- Government Page Header (Directives Section 9) -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="text-xs font-semibold uppercase tracking-wider text-gov-600 dark:text-slate-400">
                    Sistem Layanan Pendaftaran Digital
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    Persyaratan Pendaftaran Ormas (Permendagri No. 57 Tahun 2017)
                </h1>
                <div class="flex items-center gap-2 pt-1">
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">Nama Organisasi:</span>
                    <span class="font-bold text-sm text-gov-600 dark:text-slate-200 bg-gov-50 dark:bg-slate-700 px-3 py-1 rounded-md border border-gov-100 dark:border-slate-600">
                        {{ $organization->name ?? ('Organisasi ' . $user->name) }}
                    </span>
                </div>
            </div>

            <!-- Status Indicator Counter -->
            <div class="bg-slate-50 dark:bg-slate-900 px-5 py-3.5 rounded-lg border border-slate-200 dark:border-slate-700 text-center shrink-0 min-w-[200px]">
                <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    {{ $is_revision_mode ? 'Kelengkapan Berkas Revisi' : 'Kelengkapan Berkas' }}
                </span>
                <span class="block text-2xl font-extrabold text-slate-900 dark:text-white my-0.5">{{ $completed_count }} / {{ $total_items }}</span>
                <div class="w-full bg-slate-200 dark:bg-slate-700 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-gov-600 dark:bg-gov-500 h-full rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                </div>
                <span class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-1">{{ $percentage }}% Terpenuhi</span>
            </div>
        </div>
    </div>

    <!-- Mode Revisi Pengajuan Banner (Active when submission status is REVISION_NEEDED) -->
    @if($is_revision_mode && $active_submission)
        <div class="bg-gradient-to-r from-amber-600 via-amber-500 to-orange-600 text-white p-6 sm:p-7 rounded-2xl shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-black shrink-0 shadow-inner">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-black uppercase tracking-wider bg-white/25 px-2.5 py-0.5 rounded-full">
                                Mode Revisi Pengajuan
                            </span>
                            <span class="text-xs font-mono font-bold text-amber-100">
                                #{{ $active_submission->tracking_code }}
                            </span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black mt-1">
                            Perbaikan Berkas Pengajuan Ormas
                        </h2>
                    </div>
                </div>

                <a href="{{ route('user.history.detail', $active_submission->id) }}" class="px-4 py-2.5 bg-white text-orange-700 hover:bg-orange-50 font-black text-xs rounded-xl transition-all shadow-md self-start sm:self-auto flex items-center gap-2 shrink-0">
                    <i class="fa-solid fa-list-check"></i> Detail Instruksi Revisi &rarr;
                </a>
            </div>

            <div class="bg-black/15 p-4 rounded-xl text-xs space-y-1.5 leading-relaxed">
                <p class="font-extrabold text-white flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-info"></i> Petunjuk Mode Revisi:
                </p>
                <p class="text-amber-50">
                    • <strong>Berkas Disetujui:</strong> Seluruh berkas yang telah disetujui admin <strong>tetap tersimpan aman dan tidak perlu diunggah ulang</strong>.<br>
                    • <strong>Berkas Ditolak/Revisi:</strong> Anda <strong>hanya perlu mengunggah berkas yang ditandai "Wajib Revisi" atau "Ditolak"</strong>.<br>
                    • Setelah selesai mengunggah perbaikan, klik tombol <strong>"Kirim Berkas Revisi"</strong> di bagian bawah untuk mengirimkan kembali ke Admin Kesbangpol.
                </p>
                @if($latest_revision_history && $latest_revision_history->notes)
                    <div class="mt-3 pt-2.5 border-t border-white/20">
                        <span class="font-bold text-amber-100">Catatan/Instruksi Admin:</span>
                        <p class="italic text-white mt-0.5">"{{ $latest_revision_history->notes }}"</p>
                    </div>
                @endif
            </div>
        </div>
    @elseif($active_submission)
        <!-- Normal Active Submission Notification (If Not in revision mode) -->
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 text-lg shrink-0"></i>
                <div>
                    <span class="font-bold">Status Pengajuan Terakhir:</span>
                    <span class="px-2.5 py-0.5 rounded bg-amber-200 text-amber-900 font-extrabold ms-1">
                        {{ $active_submission->status->name }}
                    </span>
                    <span class="ms-2 text-slate-600 dark:text-slate-400 font-mono">#{{ $active_submission->tracking_code }}</span>
                </div>
            </div>
            <a href="{{ route('user.history.detail', $active_submission->id) }}" class="font-bold text-amber-700 hover:underline">
                Lihat Detail History &rarr;
            </a>
        </div>
    @endif

    <!-- Incomplete Warning -->
    @if(!$is_fully_complete)
        <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs">
            <div class="font-bold text-slate-900 dark:text-white mb-1">
                <i class="fa-solid fa-circle-info text-gov-600 me-1.5"></i> 
                {{ $is_revision_mode ? 'Berkas Revisi Belum Lengkap' : 'Berkas Belum Lengkap' }} 
                ({{ count($missing_titles) }} Persyaratan Belum Dipenuhi)
            </div>
            <p class="text-slate-600 dark:text-slate-400">
                Silakan lengkapi seluruh dokumen bertanda <span class="font-bold text-rose-600">WAJIB</span> di bawah ini sebelum menekan tombol {{ $is_revision_mode ? 'Kirim Berkas Revisi' : 'Kirim Berkas' }}.
            </p>
        </div>
    @endif

    <!-- Requirement #11 Conditional Section -->
    <div class="p-5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-gov-600 dark:text-slate-400">Persyaratan No. 11</span>
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                Apakah Ormas bergerak di bidang keagamaan?
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Jika <span class="font-bold text-slate-700 dark:text-slate-300">Ya</span>, upload Rekomendasi Kemenag menjadi WAJIB. Jika <span class="font-bold text-slate-700 dark:text-slate-300">Tidak</span>, persyaratan ini dianggap terpenuhi.
            </p>
        </div>

        <form action="{{ route('user.religion.update') }}" method="POST" class="flex items-center gap-2 shrink-0">
            @csrf
            <button type="submit" name="is_religious" value="1" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ $organization->is_religious ? 'bg-gov-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}">
                Ya (Keagamaan)
            </button>
            <button type="submit" name="is_religious" value="0" 
                    class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-colors {{ !$organization->is_religious ? 'bg-slate-700 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200' }}">
                Tidak
            </button>
        </form>
    </div>

    <!-- 16 Document Requirement Grid -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                    Daftar 16 Persyaratan Pengajuan Berkas
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ $is_revision_mode ? 'Periksa status masing-masing dokumen. Unggah ulang hanya dokumen yang diminta revisi atau ditolak.' : 'Kelola dan unggah dokumen persyaratan pendaftaran Ormas Kesbangpol Kota Palembang.' }}
                </p>
            </div>

            <!-- Hide delete all in revision mode to preserve approved files -->
            @if($completed_count > 0 && !$is_revision_mode)
                <form action="{{ route('user.document.delete_all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SELURUH berkas terupload (semua nomor 1 - 16)?\n\nTindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            @if(isset($active_submission) && $active_submission->is_locked) disabled title="Pengajuan sedang dikunci oleh Admin" @endif
                            class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900/80 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-xs font-bold transition-all flex items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-trash-can text-rose-600 dark:text-rose-400"></i> Hapus Semua Berkas Terupload
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($requirements as $key => $req)
                @php
                    $revStatus = $req['review']['status'] ?? 'pending';
                    $isApproved = ($revStatus === 'approved');
                    $isRevision = in_array($revStatus, ['revision_needed', 'rejected']);

                    // Card border and bg based on revision mode state
                    if ($is_revision_mode) {
                        if ($isApproved) {
                            $cardClasses = 'border-2 border-emerald-300 dark:border-emerald-800 bg-emerald-50/30 dark:bg-emerald-950/20';
                        } elseif ($isRevision && !$req['completed']) {
                            $cardClasses = 'border-2 border-amber-400 dark:border-amber-500 ring-2 ring-amber-400/30 bg-amber-50/40 dark:bg-amber-950/30';
                        } elseif ($isRevision && $req['completed']) {
                            $cardClasses = 'border-2 border-blue-300 dark:border-blue-700 bg-blue-50/30 dark:bg-blue-950/20';
                        } else {
                            $cardClasses = 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800';
                        }
                    } else {
                        $cardClasses = 'border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800';
                    }
                @endphp

                <div class="p-4 rounded-xl {{ $cardClasses }} shadow-sm space-y-3 flex flex-col justify-between transition-all">
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-7 h-7 rounded-md {{ $req['completed'] ? ($is_revision_mode && $isApproved ? 'bg-emerald-600' : 'bg-gov-600') . ' text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }} flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ $key }}
                                </span>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-900 dark:text-white leading-tight">
                                        {{ $req['title'] }}
                                    </h3>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $req['note'] }}</p>
                                </div>
                            </div>

                            <!-- Badges -->
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if($is_revision_mode && $isApproved)
                                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 text-[10px] font-black uppercase border border-emerald-300 dark:border-emerald-800">
                                        ✓ DISETUJUI ADMIN
                                    </span>
                                @elseif($is_revision_mode && $revStatus === 'rejected')
                                    <span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 text-[10px] font-black uppercase border border-rose-300 dark:border-rose-800">
                                        ❌ DITOLAK
                                    </span>
                                @elseif($is_revision_mode && $revStatus === 'revision_needed')
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 text-[10px] font-black uppercase border border-amber-300 dark:border-amber-800">
                                        ⚠️ PERLU REVISI
                                    </span>
                                @else
                                    @if($req['type'] === 'wajib')
                                        <span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 text-[10px] font-bold uppercase border border-rose-200 dark:border-rose-900">WAJIB</span>
                                    @elseif($req['type'] === 'kondisional')
                                        <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 text-[10px] font-bold uppercase border border-amber-200 dark:border-amber-900">KONDISIONAL</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300 text-[10px] font-bold uppercase">OPSIONAL</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Admin Notes (If in revision mode and has notes) -->
                        @if($is_revision_mode && $isRevision && !empty($req['review']['notes']))
                            <div class="p-2.5 rounded-lg bg-amber-100/70 dark:bg-amber-950/50 border border-amber-300 dark:border-amber-800 text-[11px] text-amber-950 dark:text-amber-200">
                                <span class="font-extrabold block mb-0.5">
                                    <i class="fa-solid fa-comment-dots me-1 text-amber-700 dark:text-amber-400"></i> Catatan/Instruksi Admin:
                                </span>
                                <span class="italic">"{{ $req['review']['notes'] }}"</span>
                            </div>
                        @endif

                        <!-- Form Kop Surat Download (Requirement #8) -->
                        @if(isset($req['has_download']) && $req['has_download'])
                            <div class="p-2.5 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-2 text-xs">
                                <span class="font-medium text-slate-700 dark:text-slate-300">Template Word Kop Surat:</span>
                                <a href="{{ $req['download_url'] }}" download class="px-3 py-1 bg-gov-600 hover:bg-gov-700 text-white rounded text-xs font-bold transition-colors">
                                    Download Form
                                </a>
                            </div>
                        @endif

                        <!-- Online Form Integration (Requirement #9 Special Flow) -->
                        @if(isset($req['is_online_form']) && $req['is_online_form'])
                            <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block">Surat Pernyataan Formulir Isian Data Ormas:</span>
                                    @if($req['completed'])
                                        <span class="text-[11px] font-extrabold text-emerald-600 dark:text-emerald-400 block mt-0.5">
                                            <i class="fa-solid fa-circle-check"></i> ✓ Berkas Terupload
                                        </span>
                                    @else
                                        <span class="text-[11px] font-bold text-amber-600 dark:text-amber-400 block mt-0.5">
                                            <i class="fa-solid fa-circle-dot"></i> Belum Terupload
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($req['completed'] && isset($req['form_data']) && $req['form_data']->excel_path)
                                        <a href="{{ route('user.form_ormas.download_excel', $req['form_data']->id) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1">
                                            <i class="fa-solid fa-file-excel"></i> Download Excel
                                        </a>
                                    @endif
                                    <a href="{{ route('user.form_ormas') }}" class="px-3.5 py-1.5 bg-gov-600 hover:bg-gov-700 text-white rounded-lg text-xs font-bold transition-colors">
                                        {{ $req['completed'] ? 'Lihat/Edit Form' : 'Isi Form' }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Sub-items for #6, #14, #15, #16 -->
                        @if(isset($req['sub_items']))
                            <div class="space-y-1.5 pt-1">
                                @foreach($req['sub_items'] as $subKey => $sub)
                                    @php
                                        $subRevSt = $sub['review']['status'] ?? 'pending';
                                        $subApproved = ($subRevSt === 'approved');
                                        $subRevision = in_array($subRevSt, ['revision_needed', 'rejected']);
                                    @endphp
                                    <div class="p-2.5 rounded-lg {{ $is_revision_mode && $subApproved ? 'bg-emerald-50/50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700' }} flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sub['title'] }}</span>
                                                @if($is_revision_mode)
                                                    @if($subApproved)
                                                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">✓ Disetujui</span>
                                                    @elseif($subRevision && !$sub['completed'])
                                                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">⚠️ Wajib Revisi</span>
                                                    @elseif($subRevision && $sub['completed'])
                                                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">✓ Revisi Terupload</span>
                                                    @endif
                                                @endif
                                            </div>
                                            @if($is_revision_mode && $subRevision && !empty($sub['review']['notes']))
                                                <p class="text-[10px] text-amber-700 dark:text-amber-400 italic">"{{ $sub['review']['notes'] }}"</p>
                                            @endif
                                        </div>

                                        <div class="shrink-0 flex items-center gap-2">
                                            @if($sub['completed'] && isset($sub['file']))
                                                <a href="{{ route('document.download', $sub['file']->id) }}" class="text-gov-600 font-bold hover:underline">Unduh</a>

                                                @if($is_revision_mode && $subApproved)
                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400">
                                                        <i class="fa-solid fa-lock"></i> Tersimpan
                                                    </span>
                                                @elseif($is_revision_mode && $subRevision)
                                                    <button type="button" 
                                                            @click="activeDocKey = '{{ $sub['key'] }}'; activeDocTitle = '{{ $sub['title'] }}'; activeDocNote = '{{ $req['note'] }}'; uploadModal = true"
                                                            class="px-2 py-0.5 bg-amber-600 hover:bg-amber-700 text-white rounded text-[10px] font-bold">
                                                        Ganti File
                                                    </button>
                                                @else
                                                    <form action="{{ route('user.document.delete', $sub['file']->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus file ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-rose-600 hover:underline">Hapus</button>
                                                    </form>
                                                @endif
                                            @else
                                                <button type="button" 
                                                        @click="activeDocKey = '{{ $sub['key'] }}'; activeDocTitle = '{{ $sub['title'] }}'; activeDocNote = '{{ $req['note'] }}'; uploadModal = true"
                                                        class="px-2.5 py-1 {{ $is_revision_mode && $subRevision ? 'bg-amber-600 hover:bg-amber-700' : 'bg-gov-600 hover:bg-gov-700' }} text-white rounded text-[11px] font-bold">
                                                    {{ $is_revision_mode && $subRevision ? 'Upload Revisi' : 'Upload' }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Action Upload for single files -->
                    @if(!isset($req['sub_items']) && !isset($req['is_online_form']))
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs">
                            <span class="text-slate-500">
                                @if($is_revision_mode && $isApproved)
                                    <span class="text-emerald-700 dark:text-emerald-400 font-bold">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Disetujui & Tersimpan Aman
                                    </span>
                                @elseif($is_revision_mode && $isRevision)
                                    @if($req['completed'])
                                        <span class="text-blue-600 dark:text-blue-400 font-bold">
                                            <i class="fa-solid fa-circle-check me-1"></i> File Revisi Telah Diunggah
                                        </span>
                                    @else
                                        <span class="text-amber-600 dark:text-amber-400 font-bold">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Wajib Diunggah Ulang
                                        </span>
                                    @endif
                                @else
                                    {{ $req['completed'] ? 'Berkas Terupload' : 'Belum diunggah' }}
                                @endif
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if(isset($req['file']) && $req['file'])
                                    <a href="{{ route('document.download', $req['file']->id) }}" class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold rounded hover:bg-slate-200 transition-colors">
                                        Unduh
                                    </a>

                                    @if($is_revision_mode && $isApproved)
                                        <!-- Locked for approved file -->
                                        <span class="px-2 py-1 text-[10px] font-bold text-slate-400 italic">Terkunci</span>
                                    @elseif($is_revision_mode && $isRevision)
                                        <!-- Re-upload option for revision -->
                                        <button type="button" 
                                                @click="activeDocKey = '{{ $req['key'] }}'; activeDocTitle = '{{ $req['title'] }}'; activeDocNote = '{{ $req['note'] }}'; uploadModal = true"
                                                class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded text-xs transition-colors">
                                            Ganti File
                                        </button>
                                    @else
                                        <form action="{{ route('user.document.delete', $req['file']->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2 py-1 text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    @endif
                                @else
                                    <button type="button" 
                                            @click="activeDocKey = '{{ $req['key'] }}'; activeDocTitle = '{{ $req['title'] }}'; activeDocNote = '{{ $req['note'] }}'; uploadModal = true"
                                            class="px-3.5 py-1.5 {{ $is_revision_mode && $isRevision ? 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700' : 'bg-gov-600 hover:bg-gov-700' }} text-white font-extrabold rounded-lg text-xs transition-all shadow-sm">
                                        {{ $is_revision_mode && $isRevision ? 'Upload Berkas Revisi' : 'Upload PDF' }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Submit Section -->
    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="space-y-0.5 text-center sm:text-left">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                {{ $is_revision_mode ? 'Kirim Dokumen Revisi ke Admin' : 'Kirim Berkas Pendaftaran' }}
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ $is_revision_mode 
                    ? 'Pastikan seluruh berkas yang diminta revisi telah diperbaiki dan diunggah ulang sebelum mengirimkan kembali.' 
                    : 'Pastikan seluruh 16 berkas telah terisi sebelum melakukan pengiriman.' }}
            </p>
        </div>

        <div>
            @if($is_fully_complete)
                <button type="button" @click="confirmModal = true" 
                        class="px-6 py-3 {{ $is_revision_mode ? 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700' : 'bg-gov-600 hover:bg-gov-700' }} text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i> {{ $is_revision_mode ? 'Kirim Berkas Revisi' : 'Kirim Berkas' }}
                </button>
            @else
                <button type="button" disabled 
                        class="px-6 py-3 bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 font-bold text-xs rounded-xl cursor-not-allowed flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation"></i> {{ $is_revision_mode ? 'Kirim Berkas Revisi (Belum Lengkap)' : 'Kirim Berkas (Belum Lengkap)' }}
                </button>
            @endif
        </div>
    </div>

    <!-- File Upload Modal -->
    <div x-show="uploadModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div @click.away="uploadModal = false" class="bg-white dark:bg-slate-800 rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 dark:border-slate-700 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="activeDocTitle"></h3>
                <button @click="uploadModal = false" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('user.document.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="document_key" :value="activeDocKey">

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Pilih File Dokumen (PDF, Maks 20 MB) <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="document" accept=".pdf,application/pdf" required
                           class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-gov-50 file:text-gov-600 hover:file:bg-gov-100">
                    <p class="text-[11px] text-slate-500 mt-1" x-text="activeDocNote"></p>
                </div>

                <!-- Requirement #16 phone and whatsapp -->
                <div x-show="['req_16a', 'req_16b', 'req_16c'].includes(activeDocKey)" class="space-y-3 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor Handphone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="phone" placeholder="08123456789" 
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nomor WhatsApp Aktif <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="whatsapp" placeholder="08123456789" 
                               class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded text-xs text-slate-900 dark:text-white">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" @click="uploadModal = false" class="px-4 py-2 rounded border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded {{ $is_revision_mode ? 'bg-amber-600 hover:bg-amber-700' : 'bg-gov-600 hover:bg-gov-700' }} text-white text-xs font-bold">
                        {{ $is_revision_mode ? 'Simpan & Unggah Revisi' : 'Simpan & Unggah' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MANDATORY Popup Confirmation Modal -->
    <div x-show="confirmModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 flex items-center justify-center p-4">
        <div @click.away="confirmModal = false" class="bg-white dark:bg-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-5 text-center">
            
            <div class="w-12 h-12 {{ $is_revision_mode ? 'bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400' : 'bg-gov-100 dark:bg-gov-950 text-gov-600 dark:text-gov-400' }} rounded-full flex items-center justify-center mx-auto text-xl font-bold">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <div class="space-y-2">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">
                    {{ $is_revision_mode ? 'Konfirmasi Pengiriman Berkas Revisi' : 'Konfirmasi Pengiriman Berkas' }}
                </h3>
                
                @if($is_revision_mode)
                    <p class="p-3.5 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200 text-xs leading-relaxed text-left">
                        "Apakah Anda yakin seluruh berkas yang diminta revisi telah diperbaiki dan diunggah dengan lengkap?
                        <br><br>
                        Berkas yang telah disetujui sebelumnya <strong>tetap tersimpan aman</strong> dan pengajuan ini akan dikirimkan kembali kepada Admin Kesbangpol untuk diverifikasi ulang."
                    </p>
                @else
                    <p class="p-3 bg-slate-50 dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs leading-relaxed">
                        "Apakah anda yakin bahwa semua data dan file sudah sepenuhnya lengkap? Jika belum tolong tekan tombol batal dan Jika sudah tolong tekan tombol Kirim"
                    </p>
                @endif
            </div>

            <form action="{{ route('user.submission.send') }}" method="POST" class="flex items-center justify-center gap-3 pt-2">
                @csrf
                <button type="button" @click="confirmModal = false" 
                        class="w-1/2 py-2.5 px-4 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                    Batal
                </button>
                
                <button type="submit" 
                        class="w-1/2 py-2.5 px-4 rounded-xl {{ $is_revision_mode ? 'bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700' : 'bg-gov-600 hover:bg-gov-700' }} text-white font-extrabold text-xs transition-colors shadow-md">
                    {{ $is_revision_mode ? 'Kirim Revisi Sekarang' : 'Kirim' }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
