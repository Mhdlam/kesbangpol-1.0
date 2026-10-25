@extends('layouts.app')

@section('title', 'Detail Organisasi - ' . $organization->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Header Breadcrumb & Status Toggle -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <a href="{{ route('admin.organizations') }}" class="text-xs font-bold text-gov-600 dark:text-sky-400 hover:underline flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Data Organisasi
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                <span>{{ $organization->name }}</span>
                @if($organization->status === 'active')
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Organisasi Aktif</span>
                @elseif($organization->status === 'inactive')
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Tidak Aktif</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-200 text-slate-800 dark:bg-slate-800 dark:text-slate-300">Diarsipkan</span>
                @endif
            </h1>
        </div>

        <!-- Status Archive Switcher (Requirement X) -->
        <form method="POST" action="{{ route('admin.organizations.update_status', $organization->id) }}" class="flex items-center gap-2">
            @csrf
            <span class="text-xs font-bold text-slate-500">Ubah Status Ormas:</span>
            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white">
                <option value="active" {{ $organization->status === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ $organization->status === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                <option value="archived" {{ $organization->status === 'archived' ? 'selected' : '' }}>Arsipkan Organisasi</option>
            </select>
        </form>
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
                                <strong>{{ $warn['organization']->name }}</strong> — {{ implode(', ', $warn['reasons']) }}
                                <a href="{{ route('admin.organizations.show', $warn['organization']->id) }}" target="_blank" class="underline font-bold ms-1">Buka Record &raquo;</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Central 7-Tab Navigation Bar (Requirement AD) -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-2 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap gap-1 text-xs font-bold">
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'profil']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'profil' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-building me-1"></i> 1. Profil
        </a>
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'pengurus']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'pengurus' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-users me-1"></i> 2. Pengurus
        </a>
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'pengajuan']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'pengajuan' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-folder-open me-1"></i> 3. Pengajuan ({{ $organization->submissions->count() }})
        </a>
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'dokumen']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'dokumen' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-file-pdf me-1"></i> 4. Dokumen
        </a>
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'kegiatan']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'kegiatan' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-calendar-check me-1"></i> 5. Kegiatan
        </a>
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'riwayat']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'riwayat' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-clock-rotate-left me-1"></i> 6. Riwayat
        </a>
        <a href="{{ route('admin.organizations.show', [$organization->id, 'tab' => 'catatan_internal']) }}"
           class="px-4 py-2.5 rounded-xl transition-all {{ $activeTab === 'catatan_internal' ? 'bg-gov-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
            <i class="fa-solid fa-lock me-1"></i> 7. Catatan Internal ({{ $organization->internalNotes->count() }})
        </a>
    </div>

    <!-- Tab 1: Profil -->
    @if($activeTab === 'profil')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Identitas Organisasi & Kontak Pendaftar</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                <div><span class="text-slate-500 block">Nama Lengkap Ormas</span><span class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $organization->name }}</span></div>
                <div><span class="text-slate-500 block">Singkatan / Akronim</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $organization->abbreviation ?? '-' }}</span></div>
                <div><span class="text-slate-500 block">Nomor Registrasi / SK</span><span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $organization->registration_number ?? '-' }}</span></div>
                <div><span class="text-slate-500 block">Universitas / Instansi Naungan</span><span class="font-semibold text-slate-800 dark:text-slate-200">{{ $organization->university ?? '-' }}</span></div>
                <div><span class="text-slate-500 block">Kategori Keagamaan</span><span class="font-semibold text-slate-800 dark:text-slate-200">{{ $organization->is_religious ? 'Ormas Keagamaan' : 'Non-Keagamaan' }}</span></div>
                <div><span class="text-slate-500 block">Tanggal Registrasi</span><span class="font-semibold text-slate-800 dark:text-slate-200">{{ $organization->created_at->format('d F Y') }}</span></div>
                
                <!-- WhatsApp Contact Section -->
                <div>
                    <span class="text-slate-500 block">Nomor WhatsApp Pendaftar</span>
                    @if($organization->user?->phone_wa)
                        @php
                            $waNumber = preg_replace('/[^0-9]/', '', $organization->user->phone_wa);
                            if (str_starts_with($waNumber, '0')) {
                                $waNumber = '62' . substr($waNumber, 1);
                            }
                        @endphp
                        <div class="flex items-center gap-2 mt-1">
                            <span class="font-extrabold text-slate-900 dark:text-white font-mono">{{ $organization->user->phone_wa }}</span>
                            <a href="https://wa.me/{{ $waNumber }}?text=Halo%20{{ urlencode($organization->name) }}%2C%20kami%20dari%20Bakesbangpol%20Kota%20Palembang" target="_blank" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm inline-flex items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>Chat Whatsapp</span>
                            </a>
                        </div>
                    @else
                        <span class="font-semibold text-slate-400">Belum didaftarkan</span>
                    @endif
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                <span class="text-slate-500 block text-xs">Alamat Lengkap Sekretariat:</span>
                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 mt-1">{{ $organization->address ?? 'Belum diisi' }}</p>
            </div>
        </div>
    @endif


    <!-- Tab 2: Pengurus -->
    @if($activeTab === 'pengurus')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Struktur Pengurus Utama</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500 block font-bold uppercase text-[10px]">Ketua Umum</span>
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm mt-1 block">{{ $organization->leader_name ?? '-' }}</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500 block font-bold uppercase text-[10px]">Sekretaris Umum</span>
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm mt-1 block">{{ $organization->secretary_name ?? '-' }}</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <span class="text-slate-500 block font-bold uppercase text-[10px]">Bendahara Umum</span>
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm mt-1 block">{{ $organization->treasurer_name ?? '-' }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 3: Pengajuan -->
    @if($activeTab === 'pengajuan')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm overflow-hidden">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-4">Riwayat Pengajuan Berkas Organisasi</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 font-bold text-slate-600 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3">Tracking Code</th>
                            <th class="px-4 py-3">Judul Pengajuan</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($organization->submissions as $sub)
                            <tr>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900 dark:text-white">#{{ $sub->tracking_code }}</td>
                                <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-200">{{ $sub->title }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $sub->created_at->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-bold text-gov-600 dark:text-sky-400">{{ $sub->status->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.submissions.show', $sub->id) }}" class="px-3 py-1 rounded bg-gov-600 text-white font-bold text-xs">Periksa &raquo;</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada submission recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Tab 4: Dokumen -->
    @if($activeTab === 'dokumen')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Seluruh Arsip Dokumen Organisasi</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($organization->submissions as $sub)
                    @foreach($sub->documents as $doc)
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs flex justify-between items-center">
                            <div>
                                <span class="font-mono font-bold text-slate-900 dark:text-white block text-[11px]">{{ $doc->document_key }}</span>
                                <span class="text-slate-600 dark:text-slate-300 font-semibold block truncate max-w-[180px]">{{ $doc->original_name }}</span>
                            </div>
                            <a href="{{ route('document.download', $doc->id) }}" class="px-2.5 py-1 rounded bg-slate-200 dark:bg-slate-700 font-bold text-slate-800 dark:text-slate-200">
                                Download
                            </a>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tab 5: Kegiatan -->
    @if($activeTab === 'kegiatan')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm text-xs text-slate-500">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white mb-2">Dokumentasi & Laporan Kegiatan Ormas</h3>
            <p>Modul dokumentasi laporan kegiatan Ormas siap diintegrasikan pada tahap berikutnya.</p>
        </div>
    @endif

    <!-- Tab 6: Riwayat -->
    @if($activeTab === 'riwayat')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Riwayat Perubahan & Audit Status</h3>
            <div class="space-y-3 text-xs">
                @foreach($organization->submissions as $sub)
                    @foreach($sub->histories as $hist)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex justify-between items-start">
                            <div>
                                <span class="font-bold text-gov-600 dark:text-sky-400">#{{ $sub->tracking_code }} — Status: {{ $hist->status->name ?? '-' }}</span>
                                <p class="text-slate-700 dark:text-slate-300 mt-1">{{ $hist->notes }}</p>
                                <span class="text-[10px] text-slate-400">Oleh: {{ $hist->changedBy->name ?? 'Sistem' }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $hist->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tab 7: Catatan Internal (Requirement AE) -->
    @if($activeTab === 'catatan_internal')
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Catatan Rahasia Internal Admin</h3>

            <form method="POST" action="{{ route('admin.organizations.internal_notes', $organization->id) }}" class="flex gap-3">
                @csrf
                <input type="text" name="note" placeholder="Tulis catatan internal organisasi (hanya admin)..." required
                       class="flex-grow px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-500 text-slate-900 font-extrabold text-xs">Simpan Catatan</button>
            </form>

            <div class="space-y-2 pt-2">
                @forelse($organization->internalNotes as $in)
                    <div class="p-3 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 text-xs flex justify-between items-start gap-3">
                        <div>
                            <span class="font-bold text-amber-900 dark:text-amber-300">{{ $in->admin->name ?? 'Admin' }}:</span>
                            <p class="text-slate-800 dark:text-slate-200 mt-0.5">{{ $in->note }}</p>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $in->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">Belum ada catatan internal.</p>
                @endforelse
            </div>
        </div>
    @endif

</div>
@endsection
