@extends('layouts.app')

@section('title', 'Formulir Isian Data Ormas (Persyaratan No. 9)')

@section('content')
<div x-data="formOrmasApp()" x-init="initSignatureCanvas()" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- Top Navigation & Status Banner -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <a href="{{ route('user.dashboard') }}" class="text-xs font-bold text-gov-600 dark:text-sky-400 hover:underline flex items-center gap-1.5 mb-2">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Dashboard User
            </a>
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Persyaratan No. 9 — Formulir Isian Data Ormas</span>
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Formulir digital pendaftaran resmi Kesbangpol Kota Palembang (Permendagri No. 57 Tahun 2017).
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($form && $form->is_confirmed)
                <span class="px-3.5 py-1.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold text-xs flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> ✓ Berkas Terupload & Dikonfirmasi
                </span>
                @if($form->excel_path)
                    <a href="{{ route('user.form_ormas.download_excel', $form->id) }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-file-excel"></i> Download Excel
                    </a>
                @endif
            @else
                <span class="px-3.5 py-1.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold text-xs">
                    Status: Belum Terupload
                </span>
            @endif
        </div>
    </div>

    <!-- Official A4 Paper Document Form Mockup Container -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 sm:p-12 relative overflow-hidden transition-colors">
        
        <!-- Document Formal Header -->
        <div class="border-b-2 border-slate-900 dark:border-slate-100 pb-6 mb-8 text-center space-y-1">
            <div class="flex justify-center items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-xl bg-gov-600 text-white flex items-center justify-center font-bold text-xl shadow-md">
                    <i class="fa-solid fa-shield"></i>
                </div>
            </div>
            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white uppercase">
                BADAN KESATUAN BANGSA DAN POLITIK KOTA PALEMBANG
            </h2>
            <p class="text-xs sm:text-sm font-black text-gov-600 dark:text-sky-400 uppercase tracking-widest">
                Persyaratan No. 8 — FORMULIR ISIAN DATA ORMAS
            </p>
            <p class="text-[11px] text-slate-500 italic">
                Sesuai Lampiran Peraturan Menteri Dalam Negeri Nomor 57 Tahun 2017
            </p>
        </div>

        <form id="formOrmas" method="POST" action="{{ route('user.form_ormas.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <input type="hidden" name="signature_data" x-model="signatureData">

            <!-- 18 Numbered Fields Grid (Exact Reference Order) -->
            <div class="space-y-5 text-xs text-slate-800 dark:text-slate-200">

                <!-- 1. Nama Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">1.</span>
                        <span>Nama Organisasi <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="nama_ormas" value="{{ old('nama_ormas', $form->nama_ormas ?? ($org->name ?? '')) }}" required
                               placeholder="Masukkan nama lengkap organisasi..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
                    </div>
                </div>

                <!-- 2. Bidang Kegiatan -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">2.</span>
                        <span>Bidang Kegiatan <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="bidang_kegiatan" value="{{ old('bidang_kegiatan', $form->bidang_kegiatan ?? '') }}" required
                               placeholder="Contoh: Kepemudaan / Keagamaan / Pendidikan / Sosial..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">
                    </div>
                </div>

                <!-- 3. Alamat Kantor/Sekretariat -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">3.</span>
                        <span>Alamat Kantor/Sekretariat <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="alamat_sekretariat" rows="3" required placeholder="Alamat jalan, RT/RW, Kelurahan, Kecamatan, Kota Palembang..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-gov-500 focus:outline-none">{{ old('alamat_sekretariat', $form->alamat_sekretariat ?? ($org->address ?? '')) }}</textarea>
                    </div>
                </div>

                <!-- 4. No. AHU / Akta Notaris -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">4.</span>
                        <span>No. AHU / Akta Notaris <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8 space-y-2">
                        <input type="text" name="nomor_akta_pendirian" value="{{ old('nomor_akta_pendirian', $form->nomor_akta_pendirian ?? '') }}" required
                               placeholder="Nomor Akta Notaris Pendirian..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <input type="text" name="nama_notaris" value="{{ old('nama_notaris', $form->nama_notaris ?? '') }}" required
                                   placeholder="Nama Notaris..."
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                            <input type="text" name="nomor_sk_kemenkumham" value="{{ old('nomor_sk_kemenkumham', $form->nomor_sk_kemenkumham ?? '') }}" required
                                   placeholder="Nomor AHU / SK Kemenkumham..."
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- 5. Tempat dan Waktu Pendirian -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">5.</span>
                        <span>Tempat & Waktu Pendirian <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="tempat_waktu_pendirian" value="{{ old('tempat_waktu_pendirian', $form->tempat_waktu_pendirian ?? '') }}" required
                               placeholder="Contoh: Palembang, 17 Agustus 2020..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>

                <!-- 6. Asas Ciri Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">6.</span>
                        <span>Asas Ciri Organisasi <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="asas_ciri_organisasi" value="{{ old('asas_ciri_organisasi', $form->asas_ciri_organisasi ?? '') }}" required
                               placeholder="Contoh: Pancasila / UUD 1945 / Islam / Kekeluargaan..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>

                <!-- 7. Tujuan Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">7.</span>
                        <span>Tujuan Organisasi <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="tujuan_organisasi" rows="3" required placeholder="Jelaskan tujuan pendirian organisasi..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">{{ old('tujuan_organisasi', $form->tujuan_organisasi ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 8. Nama Pendiri -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">8.</span>
                        <span>Nama Pendiri <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="nama_pendiri" rows="2" required placeholder="Nama lengkap para pendiri organisasi..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">{{ old('nama_pendiri', $form->nama_pendiri ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 9. Nama Pembina -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">9.</span>
                        <span>Nama Pembina <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="nama_pembina" rows="2" required placeholder="Nama pembina / dewan pembina..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">{{ old('nama_pembina', $form->nama_pembina ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 10. Nama Penasehat -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">10.</span>
                        <span>Nama Penasehat <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="nama_penasehat" rows="2" required placeholder="Nama penasehat / dewan penasehat..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">{{ old('nama_penasehat', $form->nama_penasehat ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 11. Nama Pengurus (a, b, c) -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">11.</span>
                        <span>Nama Pengurus <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8 space-y-2">
                        <div>
                            <span class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">a. Ketua / Sederajat <span class="text-rose-500">*</span></span>
                            <input type="text" name="nama_ketua" value="{{ old('nama_ketua', $form->nama_ketua ?? ($org->leader_name ?? '')) }}" required
                                   placeholder="Nama lengkap Ketua Umum..."
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">b. Sekretaris / Sederajat <span class="text-rose-500">*</span></span>
                            <input type="text" name="nama_sekretaris" value="{{ old('nama_sekretaris', $form->nama_sekretaris ?? ($org->secretary_name ?? '')) }}" required
                                   placeholder="Nama lengkap Sekretaris Umum..."
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <span class="block font-semibold text-slate-600 dark:text-slate-400 mb-1">c. Bendahara / Sederajat <span class="text-rose-500">*</span></span>
                            <input type="text" name="nama_bendahara" value="{{ old('nama_bendahara', $form->nama_bendahara ?? ($org->treasurer_name ?? '')) }}" required
                                   placeholder="Nama lengkap Bendahara Umum..."
                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <!-- 12. Masa Bhakti Kepengurusan -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">12.</span>
                        <span>Masa Bhakti Kepengurusan <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="masa_bhakti" value="{{ old('masa_bhakti', $form->masa_bhakti ?? '') }}" required
                               placeholder="Contoh: Periode 2024 - 2026 / 3 Tahun..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>

                <!-- 13. Keputusan Tertinggi Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">13.</span>
                        <span>Keputusan Tertinggi Organisasi <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="keputusan_tertinggi" value="{{ old('keputusan_tertinggi', $form->keputusan_tertinggi ?? '') }}" required
                               placeholder="Contoh: Musyawarah Daerah / Kongres / Rapat Anggota..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>

                <!-- 14. Unit/cabang/Sayap Otonom Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">14.</span>
                        <span>Unit/Sayap Otonom Organisasi <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="unit_sayap_otonom" rows="2" required placeholder="Sebutkan unit/lembaga/badan otonom jika ada..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">{{ old('unit_sayap_otonom', $form->unit_sayap_otonom ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 15. Usaha Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">15.</span>
                        <span>Usaha Organisasi <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <textarea name="usaha_organisasi" rows="2" required placeholder="Usaha/kegiatan bidang organisasi..."
                                  class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">{{ old('usaha_organisasi', $form->usaha_organisasi ?? '') }}</textarea>
                    </div>
                </div>

                <!-- 16. Sumber Keuangan -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">16.</span>
                        <span>Sumber Keuangan <span class="text-rose-500">*</span></span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8">
                        <input type="text" name="sumber_keuangan" value="{{ old('sumber_keuangan', $form->sumber_keuangan ?? '') }}" required
                               placeholder="Contoh: Iuran Anggota / Sumbangan Tidak Mengikat / Usaha Sah..."
                               class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none">
                    </div>
                </div>

                <!-- 17. Lambang/Logo Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">17.</span>
                        <span>Lambang/Logo Organisasi</span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8 space-y-2">
                        <input type="file" name="logo_file" accept="image/png,image/jpeg,image/jpg" @change="previewLogo($event)"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gov-50 file:text-gov-600 hover:file:bg-gov-100">
                        <div x-show="logoUrl || '{{ $form->logo_path ?? '' }}'" class="flex items-center gap-3 pt-1">
                            <img :src="logoUrl ? logoUrl : '{{ isset($form->logo_path) ? Storage::url($form->logo_path) : '' }}'" class="w-16 h-16 object-contain rounded-lg border border-slate-200 dark:border-slate-700 p-1 bg-white">
                            <span class="text-[11px] text-slate-500 font-medium">Preview Logo Organisasi</span>
                        </div>
                    </div>
                </div>

                <!-- 18. Bendera Organisasi -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 sm:gap-4 items-start pt-2 border-t border-slate-100 dark:border-slate-800/80">
                    <div class="sm:col-span-4 font-bold flex items-start gap-2">
                        <span class="w-6 font-mono">18.</span>
                        <span>Bendera Organisasi</span>
                        <span class="ms-auto font-bold">:</span>
                    </div>
                    <div class="sm:col-span-8 space-y-2">
                        <input type="file" name="bendera_file" accept="image/png,image/jpeg,image/jpg" @change="previewBendera($event)"
                               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-gov-50 file:text-gov-600 hover:file:bg-gov-100">
                        <div x-show="benderaUrl || '{{ $form->bendera_path ?? '' }}'" class="flex items-center gap-3 pt-1">
                            <img :src="benderaUrl ? benderaUrl : '{{ isset($form->bendera_path) ? Storage::url($form->bendera_path) : '' }}'" class="w-20 h-14 object-contain rounded-lg border border-slate-200 dark:border-slate-700 p-1 bg-white">
                            <span class="text-[11px] text-slate-500 font-medium">Preview Bendera Organisasi</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Signature & Cap Ormas Section -->
            <div class="pt-8 border-t-2 border-slate-900 dark:border-slate-100 space-y-6">
                
                <div class="text-right space-y-1">
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Palembang, {{ date('d F Y') }}
                    </span>
                    <span class="block font-bold text-xs text-slate-900 dark:text-white uppercase">
                        Ketua Ormas,
                    </span>
                </div>

                <!-- Signature & Cap Ormas Overlay Box Mockup -->
                <div class="max-w-md ms-auto space-y-4">
                    
                    <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-800/40 relative space-y-3">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block text-center">
                            Area Nama lengkap, Tanda tangan dan Cap Ormas
                        </span>

                        <!-- Signature Canvas -->
                        <div class="bg-white rounded-xl border border-slate-200 shadow-inner relative overflow-hidden">
                            <canvas id="sigCanvas" class="w-full h-36 touch-none cursor-crosshair"></canvas>
                            <span x-show="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-300 text-xs font-semibold">
                                Gambar Tanda Tangan Digital di sini (Mouse/Touch)
                            </span>
                            <button @click="clearSignature()" type="button" class="absolute bottom-2 right-2 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[10px] rounded-lg transition-colors">
                                <i class="fa-solid fa-eraser me-1"></i> Hapus Tanda Tangan
                            </button>
                        </div>

                        <!-- Cap PNG Upload & Overlay Instructions Box -->
                        <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-slate-700">
                            <div class="p-3 bg-blue-50 dark:bg-blue-950/60 rounded-xl border border-blue-200 dark:border-blue-800 text-[11px] text-blue-900 dark:text-blue-200">
                                <strong class="block font-bold mb-1"><i class="fa-solid fa-stamp text-blue-600 me-1"></i> Catatan Cap Ormas:</strong>
                                Silakan upload foto/gambar cap organisasi dalam format <strong>PNG dengan background transparan (transparent)</strong> agar cap dapat ditempatkan/ditimpa dengan rapi pada bagian tanda tangan tanpa background putih.
                            </div>

                            <input type="file" name="cap_file" accept="image/png" @change="previewCap($event)"
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-100 file:text-blue-800 hover:file:bg-blue-200">

                            <div x-show="capUrl || '{{ $form->cap_path ?? '' }}'" class="flex items-center gap-3 pt-1">
                                <div class="w-16 h-16 rounded-xl border border-slate-300 p-1 bg-slate-100 flex items-center justify-center relative overflow-hidden">
                                    <img :src="capUrl ? capUrl : '{{ isset($form->cap_path) ? Storage::url($form->cap_path) : '' }}'" class="w-full h-full object-contain">
                                </div>
                                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold">✓ Cap Ormas Transparan Dimuat</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Action Buttons Footer -->
            <div class="pt-8 border-t border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <button @click="saveDraftAjax()" type="button" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition-colors">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Draft
                </button>

                <div class="flex items-center gap-2">
                    <button @click="showPreviewModal = true" type="button" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition-colors flex items-center gap-1.5">
                        <i class="fa-solid fa-[#1e5388] fa-eye"></i> Preview Formulir
                    </button>

                    <button @click="triggerSubmitConfirmation()" type="button" class="px-6 py-2.5 rounded-xl bg-gov-600 hover:bg-gov-700 text-white font-extrabold text-xs shadow-md transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> KONFIRMASI & KIRIM FORMULIR
                    </button>
                </div>
            </div>

        </form>

    </div>

    <!-- Confirmation Modal Popup -->
    <div x-show="showConfirmPopup" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showConfirmPopup = false" class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 text-center">
            <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-950 text-2xl font-bold flex items-center justify-center mx-auto">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Konfirmasi Pengiriman Formulir</h3>
            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                Apakah Anda yakin semua data Formulir Isian Data Ormas sudah benar? Setelah dikonfirmasi, formulir akan disimpan sebagai berkas resmi dan file Excel <strong class="text-gov-600">[Nama Organisasi].xlsx</strong> akan di-generate secara otomatis.
            </p>
            <div class="flex justify-center gap-3 pt-2">
                <button @click="showConfirmPopup = false" type="button" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 font-bold text-xs">Batal</button>
                <button @click="submitFinalForm()" type="button" class="px-5 py-2 rounded-xl bg-gov-600 hover:bg-gov-700 text-white font-extrabold text-xs shadow-md">Ya, Konfirmasi & Kirim</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function formOrmasApp() {
    return {
        signatureData: '{{ $form->signature_path ?? "" }}',
        hasSignature: false,
        logoUrl: null,
        benderaUrl: null,
        capUrl: null,
        showConfirmPopup: false,
        showPreviewModal: false,
        canvas: null,
        ctx: null,
        isDrawing: false,

        initSignatureCanvas() {
            this.$nextTick(() => {
                this.canvas = document.getElementById('sigCanvas');
                if (!this.canvas) return;

                // Set internal canvas resolution
                const rect = this.canvas.getBoundingClientRect();
                this.canvas.width = rect.width || 400;
                this.canvas.height = rect.height || 150;

                this.ctx = this.canvas.getContext('2d');
                this.ctx.lineWidth = 2.5;
                this.ctx.lineCap = 'round';
                this.ctx.strokeStyle = '#0f172a';

                // Mouse Events
                this.canvas.addEventListener('mousedown', (e) => this.startDrawing(e));
                this.canvas.addEventListener('mousemove', (e) => this.draw(e));
                this.canvas.addEventListener('mouseup', () => this.stopDrawing());
                this.canvas.addEventListener('mouseleave', () => this.stopDrawing());

                // Touch Events for Mobile (Requirement Touch Canvas)
                this.canvas.addEventListener('touchstart', (e) => {
                    e.preventDefault();
                    const touch = e.touches[0];
                    const mouseEvent = new MouseEvent('mousedown', {
                        clientX: touch.clientX,
                        clientY: touch.clientY
                    });
                    this.canvas.dispatchEvent(mouseEvent);
                }, false);

                this.canvas.addEventListener('touchmove', (e) => {
                    e.preventDefault();
                    const touch = e.touches[0];
                    const mouseEvent = new MouseEvent('mousemove', {
                        clientX: touch.clientX,
                        clientY: touch.clientY
                    });
                    this.canvas.dispatchEvent(mouseEvent);
                }, false);

                this.canvas.addEventListener('touchend', (e) => {
                    e.preventDefault();
                    const mouseEvent = new MouseEvent('mouseup', {});
                    this.canvas.dispatchEvent(mouseEvent);
                }, false);
            });
        },

        getPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        },

        startDrawing(e) {
            this.isDrawing = true;
            this.hasSignature = true;
            const pos = this.getPos(e);
            this.ctx.beginPath();
            this.ctx.moveTo(pos.x, pos.y);
        },

        draw(e) {
            if (!this.isDrawing) return;
            const pos = this.getPos(e);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();
            this.signatureData = this.canvas.toDataURL('image/png');
        },

        stopDrawing() {
            if (this.isDrawing) {
                this.isDrawing = false;
                this.signatureData = this.canvas.toDataURL('image/png');
            }
        },

        clearSignature() {
            if (this.ctx && this.canvas) {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                this.hasSignature = false;
                this.signatureData = '';
            }
        },

        previewLogo(e) {
            const file = e.target.files[0];
            if (file) this.logoUrl = URL.createObjectURL(file);
        },

        previewBendera(e) {
            const file = e.target.files[0];
            if (file) this.benderaUrl = URL.createObjectURL(file);
        },

        previewCap(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.type !== 'image/png') {
                    alert('Cap Ormas Wajib berupa file gambar format PNG!');
                    e.target.value = '';
                    return;
                }
                this.capUrl = URL.createObjectURL(file);
            }
        },

        saveDraftAjax() {
            const formEl = document.getElementById('formOrmas');
            const formData = new FormData(formEl);
            if (this.signatureData) formData.set('signature_data', this.signatureData);

            fetch('{{ route("user.form_ormas.draft") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                alert('Draft Formulir berhasil disimpan!');
            })
            .catch(err => {
                formEl.action = '{{ route("user.form_ormas.draft") }}';
                formEl.submit();
            });
        },

        triggerSubmitConfirmation() {
            const formEl = document.getElementById('formOrmas');
            if (!formEl.checkValidity()) {
                formEl.reportValidity();
                return;
            }
            this.showConfirmPopup = true;
        },

        submitFinalForm() {
            const formEl = document.getElementById('formOrmas');
            formEl.action = '{{ route("user.form_ormas.store") }}';
            formEl.submit();
        }
    }
}
</script>
@endpush
@endsection
