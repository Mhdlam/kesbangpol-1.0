@extends('layouts.app')

@section('title', 'Pemeriksaan Berkas #' . $submission->tracking_code)

@section('content')
<div x-data="{ 
    pdfPreviewUrl: '', 
    pdfTitle: '', 
    showPdfModal: false,
    showRevisionModal: false,
    showApproveModal: false,
    showRejectModal: false,
    showAssignModal: false,
    pdfZoom: 100
}" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Top Breadcrumb & Action Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <a href="{{ route('admin.submissions.index') }}" class="text-xs font-bold text-gov-600 dark:text-sky-400 hover:underline flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Berkas Masuk
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>Pemeriksaan Berkas Pengajuan</span>
                <span class="px-3 py-1 rounded-full text-xs font-mono font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                    #{{ $submission->tracking_code }}
                </span>
            </h1>
        </div>

        <!-- Approval / Rejection Quick Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <button @click="showAssignModal = true" type="button" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-200 transition-colors border border-slate-300 dark:border-slate-700 flex items-center gap-1.5">
                <i class="fa-solid fa-user-gear"></i> Tugaskan: {{ $submission->assignedAdmin->name ?? 'Belum' }}
            </button>

            <a href="{{ route('admin.submissions.download_all', $submission->id) }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-file-zipper"></i> Download Semua (.zip)
            </a>

            <button @click="showRevisionModal = true" type="button" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-rotate-left"></i> Minta Revisi
            </button>

            <button @click="showRejectModal = true" type="button" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition-colors shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-xmark"></i> Tolak Berkas
            </button>

            <button @click="showApproveModal = true" type="button" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition-colors shadow-md flex items-center gap-1.5">
                <i class="fa-solid fa-check"></i> Terima Berkas
            </button>
        </div>
    </div>

    <!-- Duplicate Detection Warning Banner (Requirement L) -->
    @if(!empty($duplicateWarnings))
        <div class="bg-amber-50 dark:bg-amber-950/60 border border-amber-300 dark:border-amber-800 rounded-2xl p-4 text-amber-900 dark:text-amber-200 text-xs shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-lg mt-0.5"></i>
                <div class="space-y-1">
                    <span class="font-extrabold text-sm block">Peringatan Kemungkinan Organisasi Duplikat Detected!</span>
                    <p class="text-slate-700 dark:text-amber-300">
                        Sistem mendeteksi kemiripan data organisasi ini dengan record terdaftar berikut:
                    </p>
                    <ul class="list-disc list-inside space-y-1 mt-1 font-medium">
                        @foreach($duplicateWarnings as $warn)
                            <li>
                                <strong>{{ $warn['organization']->name }}</strong> (ID: #{{ $warn['organization']->id }}) — 
                                <span class="text-amber-800 dark:text-amber-300">{{ implode(', ', $warn['reasons']) }}</span>
                                <a href="{{ route('admin.organizations.show', $warn['organization']->id) }}" target="_blank" class="underline font-bold ms-1">Lihat Profil Organisasi &raquo;</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Information Header Grid (Requirement C) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Metadata Info Card -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-gov-600"></i> Informasi Pengajuan Berkas
            </h2>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 block">Nomor Tracking</span>
                    <span class="font-mono font-extrabold text-slate-900 dark:text-white text-sm">#{{ $submission->tracking_code }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Nama Ormas</span>
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $submission->organization->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Pemohon / User</span>
                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $submission->user->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Tanggal Pengajuan</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $submission->created_at->format('d F Y H:i') }} WIB</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Terakhir Diperbarui</span>
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $submission->updated_at->diffForHumans() }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Admin Pemeriksa</span>
                    <span class="font-extrabold text-gov-600 dark:text-sky-400">{{ $submission->assignedAdmin->name ?? 'Belum Ditugaskan' }}</span>
                </div>
            </div>
        </div>

        <!-- System Calculation Statistics Summary (Requirement D) -->
        <div class="bg-gradient-to-br from-slate-900 to-gov-800 text-white rounded-2xl p-6 shadow-md flex flex-col justify-between">
            <div>
                <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Ringkasan Hasil Pemeriksaan</span>
                <div class="text-3xl font-black text-white mt-2">
                    {{ $reqCheck['review_summary']['approved'] }} / {{ $reqCheck['total_items'] }}
                </div>
                <span class="text-xs text-slate-300 block mt-1">Dokumen Telah Memenuhi Syarat (Sesuai)</span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs pt-4 border-t border-white/10 mt-4">
                <div class="p-2 rounded-lg bg-emerald-500/20 border border-emerald-500/30">
                    <span class="block text-emerald-300 font-bold">Sesuai: {{ $reqCheck['review_summary']['approved'] }}</span>
                </div>
                <div class="p-2 rounded-lg bg-orange-500/20 border border-orange-500/30">
                    <span class="block text-orange-300 font-bold">Perlu Perbaikan: {{ $reqCheck['review_summary']['revision_needed'] }}</span>
                </div>
                <div class="p-2 rounded-lg bg-rose-500/20 border border-rose-500/30">
                    <span class="block text-rose-300 font-bold">Tidak Sesuai: {{ $reqCheck['review_summary']['rejected'] }}</span>
                </div>
                <div class="p-2 rounded-lg bg-slate-500/20 border border-slate-500/30">
                    <span class="block text-slate-300 font-bold">Belum Diperiksa: {{ $reqCheck['review_summary']['pending'] }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Workspace: 16 Requirements Individual Review Section (Requirement C & D) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200 dark:border-slate-800 pb-4 gap-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-gov-600"></i> Workspace Pemeriksaan 16 Persyaratan
                </h2>
                <p class="text-xs text-slate-500">Periksa setiap dokumen dan tetapkan status kesesuaian tanpa berpindah halaman.</p>
            </div>
            <a href="{{ route('admin.submissions.download_all', $submission->id) }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold text-xs transition-colors shadow-sm flex items-center gap-1.5 shrink-0">
                <i class="fa-solid fa-file-zipper"></i> Download Semua (.zip)
            </a>
        </div>

        <div class="space-y-4">
            @foreach($reqCheck['requirements'] as $index => $req)
                <div class="p-5 rounded-xl border {{ isset($req['review']) && $req['review']['status'] === 'approved' ? 'border-emerald-300 bg-emerald-50/20 dark:bg-emerald-950/20' : (isset($req['review']) && $req['review']['status'] === 'revision_needed' ? 'border-orange-300 bg-orange-50/20 dark:bg-orange-950/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40') }} transition-colors">
                    
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        
                        <!-- Requirement Header & File Details -->
                        <div class="space-y-1 max-w-xl">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold flex items-center justify-center">
                                    {{ $index }}
                                </span>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">
                                    {{ $req['title'] }}
                                </h3>
                                @if($req['type'] === 'wajib')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300">Wajib</span>
                                @elseif($req['type'] === 'kondisional')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">Kondisional</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300">Opsional</span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500 font-medium pl-8">{{ $req['note'] }}</p>

                            <!-- Document File Info & Quick Preview Button -->
                            <div class="pl-8 pt-2 flex flex-wrap items-center gap-3">
                                @if(isset($req['file']) && $req['file'])
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-xs font-bold">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i> {{ $req['file']->original_name }}
                                        ({{ round($req['file']->file_size / 1024) }} KB)
                                    </span>

                                    <!-- Quick Embedded Preview Button (Requirement W) -->
                                    <button @click="pdfPreviewUrl = '{{ route('document.download', $req['file']->id) }}'; pdfTitle = '{{ addslashes($req['title']) }}'; showPdfModal = true"
                                            type="button" class="px-3 py-1 rounded-lg bg-gov-600 hover:bg-gov-700 text-white font-bold text-xs transition-colors flex items-center gap-1">
                                        <i class="fa-solid fa-[#1e5388] fa-eye"></i> Preview PDF
                                    </button>

                                    <a href="{{ route('document.download', $req['file']->id) }}" download class="px-3 py-1 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-300 transition-colors flex items-center gap-1">
                                        <i class="fa-solid fa-download"></i> Download
                                    </a>
                                @elseif(isset($req['sub_items']) && count($req['sub_items']) > 0)
                                    <div class="w-full space-y-2">
                                        @foreach($req['sub_items'] as $subKey => $sub)
                                            <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs">
                                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $sub['title'] }}</span>
                                                <div class="flex items-center gap-2">
                                                    @if(isset($sub['file']) && $sub['file'])
                                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 font-medium">
                                                            <i class="fa-solid fa-file-pdf text-red-500"></i> {{ $sub['file']->original_name }} ({{ round($sub['file']->file_size / 1024) }} KB)
                                                        </span>
                                                        <button @click="pdfPreviewUrl = '{{ route('document.download', $sub['file']->id) }}'; pdfTitle = '{{ addslashes($sub['title']) }}'; showPdfModal = true"
                                                                type="button" class="px-2.5 py-1 rounded bg-gov-600 hover:bg-gov-700 text-white font-bold transition-colors flex items-center gap-1">
                                                            <i class="fa-solid fa-[#1e5388] fa-eye"></i> Preview
                                                        </button>
                                                        <a href="{{ route('document.download', $sub['file']->id) }}" download class="px-2.5 py-1 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold hover:bg-slate-300 transition-colors flex items-center gap-1">
                                                            <i class="fa-solid fa-download"></i> Download
                                                        </a>
                                                    @else
                                                        <span class="text-rose-500 font-bold italic">
                                                            <i class="fa-solid fa-circle-xmark me-1"></i> File Belum Diunggah
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(isset($req['is_online_form']) && $req['is_online_form'])
                                    @if(isset($req['form_data']) && $req['form_data'])
                                        <span class="px-3 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 text-xs font-bold">
                                            <i class="fa-solid fa-check-double me-1"></i> Form Digital {{ $req['form_data']->is_confirmed ? 'Dikonfirmasi' : 'Tersimpan' }}
                                        </span>
                                        <a href="{{ route('user.form_ormas.download_excel', $req['form_data']->id) }}" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1">
                                            <i class="fa-solid fa-file-excel"></i> Download Excel Formulir Data Ormas (.xlsx)
                                        </a>
                                    @else
                                        <span class="px-3 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/60 text-amber-800 dark:text-amber-300 text-xs font-bold">
                                            <i class="fa-solid fa-clock me-1"></i> Form Digital Belum Diisi Ormas
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-rose-500 font-bold italic">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> File Belum Diunggah
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Per-Document Verification Form -->
                        <div class="lg:w-80 bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 space-y-3">
                            <form method="POST" action="{{ route('admin.submissions.review_doc', $submission->id) }}" class="space-y-2">
                                @csrf
                                <input type="hidden" name="document_key" value="{{ $req['key'] ?? 'req_' . $index }}">

                                <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                                    Hasil Pemeriksaan Dokumen
                                </label>

                                @php
                                    $currentSt = $req['review']['status'] ?? 'pending';
                                @endphp

                                <select name="status" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-bold text-slate-900 dark:text-white">
                                    <option value="pending" {{ $currentSt === 'pending' ? 'selected' : '' }}>⏳ Belum Diperiksa</option>
                                    <option value="approved" {{ $currentSt === 'approved' ? 'selected' : '' }}>✅ Sesuai (Memenuhi Syarat)</option>
                                    <option value="revision_needed" {{ $currentSt === 'revision_needed' ? 'selected' : '' }}>⚠️ Perlu Perbaikan (Revisi)</option>
                                    <option value="rejected" {{ $currentSt === 'rejected' ? 'selected' : '' }}>❌ Tidak Sesuai</option>
                                    <option value="not_applicable" {{ $currentSt === 'not_applicable' ? 'selected' : '' }}>⚪ Tidak Berlaku</option>
                                </select>

                                <textarea name="notes" rows="2" placeholder="Berikan catatan pemeriksaan khusus dokumen ini..."
                                          class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">{{ $req['review']['notes'] ?? '' }}</textarea>

                                <button type="submit" class="w-full py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition-colors">
                                    Simpan Status Dokumen
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Document Versioning History Section (Requirements G & H) -->
    @if($submission->documentVersions->count() > 0)
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <h2 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-code-compare text-indigo-500"></i> Riwayat Versi Dokumen (Document Version Control)
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-4 py-2.5">Dokumen</th>
                            <th class="px-4 py-2.5">Versi</th>
                            <th class="px-4 py-2.5">Nama File</th>
                            <th class="px-4 py-2.5">Ukuran</th>
                            <th class="px-4 py-2.5">Waktu Upload</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($submission->documentVersions as $ver)
                            <tr>
                                <td class="px-4 py-2.5 font-bold font-mono text-slate-800 dark:text-slate-200">{{ $ver->document_key }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-950 text-indigo-800 dark:text-indigo-300 font-extrabold text-[10px]">
                                        Version {{ $ver->version }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ $ver->original_name }}</td>
                                <td class="px-4 py-2.5 text-slate-500">{{ round($ver->file_size / 1024) }} KB</td>
                                <td class="px-4 py-2.5 text-slate-500">{{ $ver->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Admin Internal Notes Section (Requirement AE) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-lock text-amber-500"></i> Catatan Internal Admin (Hanya Dilihat Admin)
        </h2>
        
        <form method="POST" action="{{ route('admin.submissions.internal_notes', $submission->id) }}" class="flex gap-3">
            @csrf
            <input type="text" name="note" placeholder="Tulis catatan rahasia internal admin..." required
                   class="flex-grow px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
            <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-900 font-extrabold text-xs transition-colors">
                Tambah Catatan
            </button>
        </form>

        <div class="space-y-2 pt-2">
            @forelse($submission->internalNotes as $in)
                <div class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 text-xs flex justify-between items-start gap-3">
                    <div>
                        <span class="font-bold text-amber-900 dark:text-amber-300">{{ $in->admin->name ?? 'Admin' }}:</span>
                        <p class="text-slate-800 dark:text-slate-200 mt-0.5">{{ $in->note }}</p>
                    </div>
                    <span class="text-[10px] text-slate-400 whitespace-nowrap">{{ $in->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400 italic">Belum ada catatan internal admin.</p>
            @endforelse
        </div>
    </div>


    <!-- ================= MODALS SECTION ================= -->

    <!-- 1. PDF Embedded Viewer Modal (Requirement W) -->
    <div x-show="showPdfModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showPdfModal = false" class="bg-slate-900 text-white w-full max-w-5xl h-[85vh] rounded-2xl flex flex-col shadow-2xl overflow-hidden border border-slate-800">
            <!-- Modal Header -->
            <div class="p-4 bg-slate-950 border-b border-slate-800 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-file-pdf text-red-500 text-xl"></i>
                    <div>
                        <h3 class="font-bold text-sm text-white" x-text="pdfTitle">Preview Dokumen</h3>
                        <span class="text-xs text-slate-400">Viewer PDF In-System Kesbangpol</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="pdfPreviewUrl" download class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-bold text-white transition-colors">
                        <i class="fa-solid fa-download"></i> Download
                    </a>
                    <button @click="showPdfModal = false" type="button" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            <!-- PDF Iframe Viewer -->
            <div class="flex-grow bg-slate-950 relative">
                <iframe :src="pdfPreviewUrl" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

    <!-- 2. Revision Request Modal (Requirement F) -->
    <div x-show="showRevisionModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showRevisionModal = false" class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-3">
                <h3 class="font-extrabold text-base text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-rotate-left text-orange-500"></i> Kirim Permintaan Revisi Dokumen
                </h3>
                <button @click="showRevisionModal = false" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form method="POST" action="{{ route('admin.submissions.request_revision', $submission->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        Pilih Dokumen yang Membutuhkan Revisi:
                    </label>
                    <div class="max-h-40 overflow-y-auto space-y-1.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-xs">
                        @foreach($reqCheck['requirements'] as $req)
                            <label class="flex items-center gap-2 text-slate-800 dark:text-slate-200 cursor-pointer">
                                <input type="checkbox" name="revision_keys[]" value="{{ $req['key'] ?? 'req_' . $loop->iteration }}" class="rounded border-slate-300">
                                <span>{{ $req['title'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Instruksi Revisi (Tampil di Dashboard User):
                    </label>
                    <textarea name="revision_notes" rows="4" required placeholder="Contoh: Mohon upload SK kepengurusan terbaru dengan tanda tangan yang dapat dibaca..."
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button @click="showRevisionModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-extrabold text-xs shadow-md">Kirim Permintaan Revisi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Final Approve Confirmation Modal (Requirement I) -->
    <div x-show="showApproveModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showApproveModal = false" class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 text-center">
            <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400 flex items-center justify-center text-2xl font-bold mx-auto">
                <i class="fa-solid fa-circle-check"></i>
            </div>

            <h3 class="font-extrabold text-lg text-slate-900 dark:text-white">Konfirmasi Terima Berkas</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400">
                Apakah Anda yakin seluruh 16 persyaratan telah diperiksa dan berkas pendaftaran Ormas ini dapat **DITERIMA**?
            </p>

            <form method="POST" action="{{ route('admin.submissions.approve', $submission->id) }}" class="flex justify-center gap-3 pt-3">
                @csrf
                <button @click="showApproveModal = false" type="button" class="px-5 py-2.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md">
                    Ya, Terima Berkas
                </button>
            </form>
        </div>
    </div>

    <!-- 4. Reject Confirmation Modal (Requirement I) -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showRejectModal = false" class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950 text-base font-bold flex items-center justify-center">
                    <i class="fa-solid fa-xmark"></i>
                </div>
                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Konfirmasi Penolakan Berkas</h3>
            </div>

            <form method="POST" action="{{ route('admin.submissions.reject', $submission->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Alasan Wajib Penolakan Berkas:
                    </label>
                    <textarea name="rejection_reason" rows="4" required placeholder="Jelaskan alasan hukum / kelengkapan teknis mengapa berkas ini ditolak..."
                              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button @click="showRejectModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md">Tolak Berkas Submission</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. Assign Examiner Admin Modal (Requirement J) -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAssignModal = false" class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-3">
                Tugaskan Admin Pemeriksa
            </h3>

            <form method="POST" action="{{ route('admin.submissions.assign', $submission->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Pilih Admin:</label>
                    <select name="admin_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                        @foreach($admins as $adm)
                            <option value="{{ $adm->id }}" {{ $submission->assigned_admin_id == $adm->id ? 'selected' : '' }}>
                                {{ $adm->name }} ({{ $adm->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button @click="showAssignModal = false" type="button" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-gov-600 text-white font-bold text-xs shadow-md">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
