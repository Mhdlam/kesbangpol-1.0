<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationForm;
use App\Services\ActivityLogger;
use App\Services\ExcelFormGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizationFormController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $org = $user->organization ?? Organization::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'Organisasi ' . $user->name]
        );

        $form = OrganizationForm::where('user_id', $user->id)->first();

        return view('user.form_ormas', compact('user', 'org', 'form'));
    }

    public function saveDraft(Request $request)
    {
        $user = Auth::user();
        $org = $user->organization ?? Organization::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'Organisasi ' . $user->name]
        );

        $form = OrganizationForm::firstOrNew(['user_id' => $user->id]);
        $form->organization_id = $org->id;

        // Fill all provided draft fields without enforcing mandatory validation
        $form->fill($request->only([
            'nama_ormas', 'bidang_kegiatan', 'alamat_sekretariat', 'nomor_akta_pendirian',
            'nama_notaris', 'nomor_sk_kemenkumham', 'tempat_waktu_pendirian', 'asas_ciri_organisasi',
            'tujuan_organisasi', 'nama_pendiri', 'nama_pembina', 'nama_penasehat',
            'nama_ketua', 'nama_sekretaris', 'nama_bendahara', 'masa_bhakti',
            'keputusan_tertinggi', 'unit_sayap_otonom', 'usaha_organisasi', 'sumber_keuangan'
        ]));

        // Logo Upload Draft
        if ($request->hasFile('logo_file')) {
            $logo = $request->file('logo_file');
            $logoPath = $logo->storeAs('uploads/logo/' . $user->id, Str::uuid() . '.' . $logo->getClientOriginalExtension(), 'local');
            $form->logo_path = $logoPath;
        }

        // Bendera Upload Draft
        if ($request->hasFile('bendera_file')) {
            $bendera = $request->file('bendera_file');
            $benderaPath = $bendera->storeAs('uploads/bendera/' . $user->id, Str::uuid() . '.' . $bendera->getClientOriginalExtension(), 'local');
            $form->bendera_path = $benderaPath;
        }

        // Cap PNG Upload Draft
        if ($request->hasFile('cap_file')) {
            $request->validate([
                'cap_file' => ['mimes:png', 'max:5120'],
            ], ['cap_file.mimes' => 'Cap Ormas wajib berupa file gambar format PNG.']);

            $cap = $request->file('cap_file');
            $capPath = $cap->storeAs('uploads/cap/' . $user->id, Str::uuid() . '.png', 'local');
            $form->cap_path = $capPath;
        }

        // Signature Canvas Draft
        if ($request->filled('signature_data')) {
            $sigData = $request->input('signature_data');
            if (str_contains($sigData, 'base64,')) {
                $base64 = explode('base64,', $sigData)[1];
                $decoded = base64_decode($base64);
                $sigPath = 'signatures/' . $user->id . '/sig_' . time() . '.png';
                Storage::disk('local')->put($sigPath, $decoded);
                $form->signature_path = $sigPath;
            }
        }

        $form->is_confirmed = false; // Draft stays unconfirmed
        $form->save();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Draft berhasil disimpan.']);
        }

        return redirect()->back()->with('success', 'Draft Formulir Isian Data Ormas berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ormas' => ['required', 'string', 'max:255'],
            'bidang_kegiatan' => ['required', 'string', 'max:255'],
            'alamat_sekretariat' => ['required', 'string'],
            'nomor_akta_pendirian' => ['required', 'string', 'max:255'],
            'nama_notaris' => ['required', 'string', 'max:255'],
            'nomor_sk_kemenkumham' => ['required', 'string', 'max:255'],
            'tempat_waktu_pendirian' => ['required', 'string', 'max:255'],
            'asas_ciri_organisasi' => ['required', 'string', 'max:255'],
            'tujuan_organisasi' => ['required', 'string'],
            'nama_pendiri' => ['required', 'string'],
            'nama_pembina' => ['required', 'string'],
            'nama_penasehat' => ['required', 'string'],
            'nama_ketua' => ['required', 'string', 'max:255'],
            'nama_sekretaris' => ['required', 'string', 'max:255'],
            'nama_bendahara' => ['required', 'string', 'max:255'],
            'masa_bhakti' => ['required', 'string', 'max:255'],
            'keputusan_tertinggi' => ['required', 'string', 'max:255'],
            'unit_sayap_otonom' => ['required', 'string'],
            'usaha_organisasi' => ['required', 'string'],
            'sumber_keuangan' => ['required', 'string', 'max:255'],
            'logo_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'bendera_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:5120'],
            'cap_file' => ['nullable', 'file', 'mimes:png', 'max:5120'],
            'signature_data' => ['nullable', 'string'],
        ], [
            'nama_ormas.required' => 'Nama Organisasi (Field 1) wajib diisi.',
            'bidang_kegiatan.required' => 'Bidang Kegiatan (Field 2) wajib diisi.',
            'alamat_sekretariat.required' => 'Alamat Kantor/Sekretariat (Field 3) wajib diisi.',
            'nomor_akta_pendirian.required' => 'No. Akta Notaris (Field 4) wajib diisi.',
            'nama_notaris.required' => 'Nama Notaris (Field 4) wajib diisi.',
            'nomor_sk_kemenkumham.required' => 'No. SK Kemenkumham (Field 4) wajib diisi.',
            'tempat_waktu_pendirian.required' => 'Tempat dan Waktu Pendirian (Field 5) wajib diisi.',
            'asas_ciri_organisasi.required' => 'Asas Ciri Organisasi (Field 6) wajib diisi.',
            'tujuan_organisasi.required' => 'Tujuan Organisasi (Field 7) wajib diisi.',
            'nama_pendiri.required' => 'Nama Pendiri (Field 8) wajib diisi.',
            'nama_pembina.required' => 'Nama Pembina (Field 9) wajib diisi.',
            'nama_penasehat.required' => 'Nama Penasehat (Field 10) wajib diisi.',
            'nama_ketua.required' => 'Nama Ketua (Field 11a) wajib diisi.',
            'nama_sekretaris.required' => 'Nama Sekretaris (Field 11b) wajib diisi.',
            'nama_bendahara.required' => 'Nama Bendahara (Field 11c) wajib diisi.',
            'masa_bhakti.required' => 'Masa Bhakti Kepengurusan (Field 12) wajib diisi.',
            'keputusan_tertinggi.required' => 'Keputusan Tertinggi Organisasi (Field 13) wajib diisi.',
            'unit_sayap_otonom.required' => 'Unit/Sayap Otonom (Field 14) wajib diisi.',
            'usaha_organisasi.required' => 'Usaha Organisasi (Field 15) wajib diisi.',
            'sumber_keuangan.required' => 'Sumber Keuangan (Field 16) wajib diisi.',
            'cap_file.mimes' => 'Cap Ormas wajib berupa file gambar format PNG transparan.',
        ]);

        $user = Auth::user();
        $org = $user->organization ?? Organization::create([
            'user_id' => $user->id,
            'name' => $validated['nama_ormas'],
        ]);

        // Sync Organization Model
        $org->update([
            'name' => $validated['nama_ormas'],
            'address' => $validated['alamat_sekretariat'],
            'leader_name' => $validated['nama_ketua'],
            'secretary_name' => $validated['nama_sekretaris'],
            'treasurer_name' => $validated['nama_bendahara'],
        ]);

        $form = OrganizationForm::firstOrNew(['user_id' => $user->id]);
        $form->organization_id = $org->id;
        $form->fill($validated);

        // Logo Upload
        if ($request->hasFile('logo_file')) {
            $logo = $request->file('logo_file');
            $logoPath = $logo->storeAs('uploads/logo/' . $user->id, Str::uuid() . '.' . $logo->getClientOriginalExtension(), 'local');
            $form->logo_path = $logoPath;
        }

        // Bendera Upload
        if ($request->hasFile('bendera_file')) {
            $bendera = $request->file('bendera_file');
            $benderaPath = $bendera->storeAs('uploads/bendera/' . $user->id, Str::uuid() . '.' . $bendera->getClientOriginalExtension(), 'local');
            $form->bendera_path = $benderaPath;
        }

        // Cap PNG Upload
        if ($request->hasFile('cap_file')) {
            $cap = $request->file('cap_file');
            $capPath = $cap->storeAs('uploads/cap/' . $user->id, Str::uuid() . '.png', 'local');
            $form->cap_path = $capPath;
        }

        // Signature Canvas Base64 Conversion
        if ($request->filled('signature_data')) {
            $sigData = $request->input('signature_data');
            if (str_contains($sigData, 'base64,')) {
                $base64 = explode('base64,', $sigData)[1];
                $decoded = base64_decode($base64);
                $sigPath = 'signatures/' . $user->id . '/sig_' . time() . '.png';
                Storage::disk('local')->put($sigPath, $decoded);
                $form->signature_path = $sigPath;
            }
        }

        // Validate that signature is provided
        if (!$form->signature_path && !$request->filled('signature_data')) {
            return redirect()->back()->withInput()->with('error', 'Tanda tangan digital Ketua Ormas wajib digambar pada canvas.');
        }

        // Final Confirmation Updates
        $form->is_confirmed = true;
        $form->confirmed_at = now();
        $form->completed_at = now();
        $form->save();

        // Automated Excel Generation ([Nama Organisasi].xlsx)
        ExcelFormGenerator::generate($form);

        ActivityLogger::log('confirm_form_ormas', "Mengonfirmasi dan mengirim Formulir Isian Data Ormas (Persyaratan No. 9)", 'OrganizationForm', $form->id);

        return redirect()->route('user.dashboard')
            ->with('success', '✓ Formulir Isian Data Ormas berhasil dikonfirmasi dan file Excel (' . $validated['nama_ormas'] . '.xlsx) telah di-generate secara otomatis. Status Berkas No. 9 kini BERKAS TERUPLOAD.');
    }

    public function downloadExcel(OrganizationForm $form)
    {
        $user = Auth::user();

        // Authorization check: User can download their own form Excel, Admin can download any form Excel
        if ($user->role !== 'admin' && $form->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk mendownload file Excel ini.');
        }

        if (!$form->excel_path || !Storage::disk('local')->exists($form->excel_path)) {
            // Re-generate if missing
            ExcelFormGenerator::generate($form);
        }

        $fullPath = storage_path('app/' . $form->excel_path);
        $downloadFileName = Str::slug($form->nama_ormas, ' ') . '.xlsx';

        return response()->download($fullPath, $downloadFileName);
    }
}
