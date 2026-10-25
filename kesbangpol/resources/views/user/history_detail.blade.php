@extends('layouts.app')

@section('title', 'Detail History Pengajuan')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
    <div>
        <a href="{{ route('user.history') }}" class="text-xs font-bold text-red-600 dark:text-red-400 hover:underline flex items-center gap-1.5 mb-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Semua Berkas
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">
                    Detail Pengajuan #{{ $submission->tracking_code }}
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ $submission->title }}
                </p>
            </div>

            @php
                $badgeClasses = match($submission->status->code ?? '') {
                    'approved' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200 border-emerald-300 dark:border-emerald-800',
                    'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200 border-rose-300 dark:border-rose-800',
                    'revision_needed' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-200 border-amber-300 dark:border-amber-800',
                    default => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-200 border-sky-300 dark:border-sky-800',
                };
            @endphp
            <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-extrabold border {{ $badgeClasses }}">
                <i class="fa-solid fa-circle-dot me-1.5 animate-pulse"></i> {{ $submission->status->name }}
            </span>
        </div>
    </div>

    <!-- Alert / Information Section When Status is REVISION_NEEDED -->
    @if($submission->status && $submission->status->code === \App\Models\Status::REVISION_NEEDED)
        <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-amber-500/10 border-2 border-amber-400 dark:border-amber-600 rounded-3xl p-6 sm:p-8 shadow-xl space-y-6">
            <!-- Header of Revision Notice -->
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-amber-200 dark:border-amber-800/80 pb-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800 mb-1.5">
                            Instruksi Revisi Berkas Pengajuan
                        </span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">
                            Pengajuan Memerlukan Perbaikan
                        </h2>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                            Pemeriksa: <strong class="text-slate-800 dark:text-white">{{ $submission->assignedAdmin->name ?? 'Admin Kesbangpol' }}</strong> 
                            • Waktu Instruksi: <span class="font-medium">{{ $latestRevisionHistory ? $latestRevisionHistory->created_at->translatedFormat('d F Y, H:i') : ($submission->reviewed_at ? $submission->reviewed_at->translatedFormat('d F Y, H:i') : '-') }} WIB</span>
                        </p>
                    </div>
                </div>

                <!-- Main Action Button "Revisi Sekarang" -->
                <form action="{{ route('user.submission.start_revision', $submission->id) }}" method="POST" class="shrink-0">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-black text-sm rounded-2xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2.5">
                        <i class="fa-solid fa-pen-to-square text-base"></i> Revisi Sekarang
                    </button>
                </form>
            </div>

            <!-- General Revision / Admin Instructions Note -->
            @if($latestRevisionHistory && $latestRevisionHistory->notes)
                <div class="p-4 rounded-2xl bg-amber-100/60 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-800 space-y-1">
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-900 dark:text-amber-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-message"></i> Catatan & Instruksi Perbaikan dari Admin:
                    </span>
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 italic leading-relaxed">
                        "{{ $latestRevisionHistory->notes }}"
                    </p>
                </div>
            @endif

            <!-- List of Problematic Documents (Wajib Direvisi / Ditolak) -->
            @php
                $problematicDocs = [];
                $approvedDocs = [];

                if (isset($reqCheck['requirements'])) {
                    foreach ($reqCheck['requirements'] as $reqNum => $reqItem) {
                        if (isset($reqItem['sub_items'])) {
                            foreach ($reqItem['sub_items'] as $subKey => $subItem) {
                                $st = $subItem['review']['status'] ?? 'pending';
                                if (in_array($st, ['revision_needed', 'rejected'])) {
                                    $problematicDocs[] = [
                                        'number' => $reqNum . ' (' . $subKey . ')',
                                        'title' => $reqItem['title'] . ' - ' . $subItem['title'],
                                        'status' => $st,
                                        'notes' => $subItem['review']['notes'] ?? null,
                                        'file' => $subItem['file'] ?? null,
                                    ];
                                } elseif ($st === 'approved') {
                                    $approvedDocs[] = [
                                        'number' => $reqNum . ' (' . $subKey . ')',
                                        'title' => $reqItem['title'] . ' - ' . $subItem['title'],
                                    ];
                                }
                            }
                        } else {
                            $st = $reqItem['review']['status'] ?? 'pending';
                            if (in_array($st, ['revision_needed', 'rejected'])) {
                                $problematicDocs[] = [
                                    'number' => $reqNum,
                                    'title' => $reqItem['title'],
                                    'status' => $st,
                                    'notes' => $reqItem['review']['notes'] ?? null,
                                    'file' => $reqItem['file'] ?? null,
                                ];
                            } elseif ($st === 'approved') {
                                $approvedDocs[] = [
                                    'number' => $reqNum,
                                    'title' => $reqItem['title'],
                                ];
                            }
                        }
                    }
                }
            @endphp

            @if(count($problematicDocs) > 0)
                <div class="space-y-3">
                    <h3 class="text-xs font-black uppercase tracking-wider text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-exclamation"></i> Berkas yang Perlu Direvisi / Ditolak (Wajib Diupload Ulang)
                    </h3>
                    <div class="grid grid-cols-1 gap-3">
                        @foreach($problematicDocs as $pDoc)
                            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border-2 border-rose-200 dark:border-rose-900 shadow-sm space-y-2">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-md bg-rose-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                                            {{ $pDoc['number'] }}
                                        </span>
                                        <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white">
                                            {{ $pDoc['title'] }}
                                        </h4>
                                    </div>
                                    @if($pDoc['status'] === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800 inline-flex items-center gap-1 self-start sm:self-auto">
                                            <i class="fa-solid fa-circle-xmark"></i> Ditolak Admin
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800 inline-flex items-center gap-1 self-start sm:self-auto">
                                            <i class="fa-solid fa-triangle-exclamation"></i> Perlu Direvisi
                                        </span>
                                    @endif
                                </div>

                                <div class="p-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900 text-xs">
                                    <span class="font-bold text-rose-800 dark:text-rose-300 block mb-0.5">
                                        <i class="fa-solid fa-comment-dots me-1"></i> Alasan / Catatan Perbaikan Admin:
                                    </span>
                                    <p class="text-slate-700 dark:text-slate-300 italic">
                                        {{ $pDoc['notes'] ?: 'Harap periksa kelengkapan dan format file lalu unggah ulang dokumen yang sesuai.' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- List of Approved Documents (Tersimpan & Tidak Perlu Diunggah Ulang) -->
            @if(count($approvedDocs) > 0)
                <div class="space-y-2 pt-2 border-t border-amber-200 dark:border-amber-800/60">
                    <h3 class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> Berkas yang Telah Disetujui (Tersimpan Aman & Tidak Perlu Diunggah Ulang)
                    </h3>
                    <div class="p-3.5 rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-700 dark:text-slate-300">
                            @foreach($approvedDocs as $aDoc)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-check text-emerald-600 dark:text-emerald-400 shrink-0"></i>
                                    <span class="truncate"><strong>No. {{ $aDoc['number'] }}:</strong> {{ $aDoc['title'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="p-4 rounded-2xl bg-white/70 dark:bg-slate-900/70 border border-amber-300 dark:border-amber-700/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-lightbulb text-amber-500 text-lg"></i>
                    <span>Klik tombol <strong>"Revisi Sekarang"</strong> di atas atau di samping untuk mulai mengunggah berkas yang bermasalah di Dashboard.</span>
                </div>
                <form action="{{ route('user.submission.start_revision', $submission->id) }}" method="POST" class="shrink-0 w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-black text-xs rounded-xl shadow transition-colors flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-pen-to-square"></i> Mulai Revisi di Dashboard
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Alert Section When Status is REJECTED -->
    @if($submission->status && $submission->status->code === \App\Models\Status::REJECTED)
        @php
            $rejectStatus = \App\Models\Status::where('code', \App\Models\Status::REJECTED)->first();
            $rejectLog = $submission->histories->where('status_id', $rejectStatus?->id)->first();
        @endphp
        <div class="bg-rose-50 dark:bg-rose-950/40 border-2 border-rose-300 dark:border-rose-800 rounded-3xl p-6 sm:p-8 shadow-xl space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <div>
                    <h2 class="text-xl font-black text-rose-900 dark:text-rose-200">Pengajuan Berkas Ditolak</h2>
                    <p class="text-xs text-rose-700 dark:text-rose-400">Pengajuan ini tidak dapat disetujui oleh Badan Kesbangpol Kota Palembang.</p>
                </div>
            </div>
            @if($rejectLog && $rejectLog->notes)
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-800 text-xs">
                    <span class="font-bold text-rose-800 dark:text-rose-300 block mb-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Alasan Penolakan:
                    </span>
                    <p class="text-slate-800 dark:text-slate-200 italic leading-relaxed">
                        "{{ $rejectLog->notes }}"
                    </p>
                </div>
            @endif
        </div>
    @endif

    <!-- Uploaded Documents in this Submission -->
    <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-2">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-red-600">
                <i class="fa-solid fa-folder me-1.5"></i> Dokumen Terlampir Dalam Pengajuan ini
            </h3>
            <span class="text-xs font-bold text-slate-500">
                Total: {{ $submission->documents->count() }} Dokumen
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @forelse($submission->documents as $doc)
                @php
                    $docRev = $submission->documentReviews->where('document_key', $doc->document_key)->first();
                @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                    <div class="truncate max-w-[240px] space-y-0.5">
                        <span class="font-bold text-slate-800 dark:text-slate-200 block truncate">{{ $doc->original_name }}</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-slate-500 uppercase">{{ $doc->document_key }} ({{ round($doc->file_size / 1024) }} KB)</span>
                            @if($docRev)
                                @if($docRev->status === 'approved')
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">✓ Sesuai</span>
                                @elseif($docRev->status === 'revision_needed')
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">⚠️ Revisi</span>
                                @elseif($docRev->status === 'rejected')
                                    <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">❌ Ditolak</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('document.download', $doc->id) }}" class="px-3 py-1.5 rounded-lg bg-red-700 hover:bg-red-800 text-white font-bold text-xs shadow-sm">
                        <i class="fa-solid fa-download me-1"></i> Unduh
                    </a>
                </div>
            @empty
                <div class="col-span-2 text-center py-6 text-slate-400 text-xs">
                    Belum ada dokumen yang terhubung dengan pengajuan ini.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Status History Timeline -->
    <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xl space-y-4">
        <h3 class="text-sm font-extrabold uppercase tracking-wider text-red-600 border-b border-slate-200 dark:border-slate-700 pb-2">
            <i class="fa-solid fa-timeline me-1.5"></i> Riwayat & Log Perubahan Status
        </h3>

        <div class="relative border-l-2 border-slate-200 dark:border-slate-700 left-3 space-y-6 my-4 pl-6">
            @foreach($submission->histories as $log)
                <div class="relative">
                    <div class="absolute -left-9 top-0.5 w-6 h-6 rounded-full bg-red-600 text-white flex items-center justify-center text-[10px] shadow">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <span class="inline-block px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold">
                            {{ $log->status->name }}
                        </span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 ms-2">
                            {{ $log->created_at->translatedFormat('d F Y, H:i') }} WIB
                        </span>
                        @if($log->notes)
                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 italic">
                                "{{ $log->notes }}"
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
