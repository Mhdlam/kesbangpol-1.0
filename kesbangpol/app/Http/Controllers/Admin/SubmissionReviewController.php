<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminInternalNote;
use App\Models\Status;
use App\Models\Submission;
use App\Models\SubmissionDocumentReview;
use App\Models\SubmissionHistory;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\DuplicateDetector;
use App\Services\RequirementChecker;
use App\Models\SubmissionDocument;
use App\Models\OrganizationForm;
use App\Services\ExcelFormGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionReviewController extends Controller
{
    public function show(Submission $submission)
    {
        $submission->load([
            'user.organization', 
            'status', 
            'documents', 
            'histories.status', 
            'histories.changedBy', 
            'assignedAdmin', 
            'documentReviews.admin', 
            'documentVersions', 
            'internalNotes.admin'
        ]);

        $admin = Auth::user();

        // Auto record review_started_at if not set
        if (!$submission->review_started_at) {
            $submission->update([
                'review_started_at' => now(),
                'assigned_admin_id' => $submission->assigned_admin_id ?: $admin->id,
                'assigned_at' => $submission->assigned_at ?: now(),
            ]);
        }

        // If status is SUBMITTED, update to IN_PROCESS on first review open
        if ($submission->status && $submission->status->code === Status::SUBMITTED) {
            $inProcessStatus = Status::where('code', Status::IN_PROCESS)->first();
            if ($inProcessStatus) {
                $submission->update(['status_id' => $inProcessStatus->id]);
            }
        }

        $reqCheck = RequirementChecker::checkUserStatus($submission->user, $submission);
        $statuses = Status::all();
        $admins = User::where('role', 'admin')->get();

        // Duplicate detector check for organization
        $duplicateWarnings = [];
        if ($submission->organization) {
            $duplicateWarnings = DuplicateDetector::detectDuplicates($submission->organization);
        }

        return view('admin.submission_detail', compact('submission', 'reqCheck', 'statuses', 'admins', 'duplicateWarnings'));
    }

    public function reviewDocument(Request $request, Submission $submission)
    {
        $request->validate([
            'document_key' => ['required', 'string'],
            'status' => ['required', 'in:approved,revision_needed,rejected,pending,not_applicable'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'status.required' => 'Status pemeriksaan dokumen wajib dipilih.',
            'status.in' => 'Status pemeriksaan dokumen tidak valid.',
        ]);

        $admin = Auth::user();

        SubmissionDocumentReview::updateOrCreate(
            [
                'submission_id' => $submission->id,
                'document_key' => $request->input('document_key'),
            ],
            [
                'admin_id' => $admin->id,
                'status' => $request->input('status'),
                'notes' => $request->input('notes'),
            ]
        );

        $submission->update([
            'reviewed_at' => now(),
        ]);

        ActivityLogger::log('review_doc', "Pemeriksaan dokumen {$request->input('document_key')} pada pengajuan #{$submission->tracking_code} diset: " . strtoupper($request->input('status')), 'Submission', $submission->id);

        return redirect()->back()->with('success', 'Status hasil pemeriksaan dokumen berhasil disimpan.');
    }

    public function requestRevision(Request $request, Submission $submission)
    {
        $request->validate([
            'revision_notes' => ['required', 'string', 'min:5'],
            'revision_keys' => ['nullable', 'array'],
        ], [
            'revision_notes.required' => 'Catatan instruksi revisi wajib diisi untuk Ormas.',
            'revision_notes.min' => 'Catatan instruksi revisi minimal 5 karakter.',
        ]);

        $admin = Auth::user();
        $revisionStatus = Status::where('code', Status::REVISION_NEEDED)->firstOrFail();

        // Tag selected document keys as revision_needed
        if ($request->has('revision_keys')) {
            foreach ($request->input('revision_keys') as $key) {
                SubmissionDocumentReview::updateOrCreate(
                    ['submission_id' => $submission->id, 'document_key' => $key],
                    ['admin_id' => $admin->id, 'status' => 'revision_needed', 'notes' => $request->input('revision_notes')]
                );
            }
        }

        $submission->update([
            'status_id' => $revisionStatus->id,
            'is_locked' => true, // Submission is locked, only flagged revision docs open for edit
            'revision_count' => $submission->revision_count + 1,
            'reviewed_at' => now(),
        ]);

        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'status_id' => $revisionStatus->id,
            'changed_by_user_id' => $admin->id,
            'notes' => 'Catatan Revisi dari Admin: ' . $request->input('revision_notes'),
        ]);

        ActivityLogger::log('request_revision', "Meminta revisi pengajuan #{$submission->tracking_code} kepada Ormas {$submission->user->name}", 'Submission', $submission->id);

        ActivityLogger::notifyUser(
            $submission->user_id,
            'revision_requested',
            'Permintaan Revisi Berkas Pendaftaran',
            "Admin Kesbangpol meminta revisi dokumen pada pengajuan #{$submission->tracking_code}. Catatan: {$request->input('revision_notes')}",
            route('user.history.detail', $submission->id)
        );

        return redirect()->back()->with('success', 'Permintaan revisi berhasil dikirim kepada Ormas.');
    }

    public function approve(Request $request, Submission $submission)
    {
        $admin = Auth::user();
        $approvedStatus = Status::where('code', Status::APPROVED)->firstOrFail();

        $submission->update([
            'status_id' => $approvedStatus->id,
            'is_locked' => true,
            'completed_at' => now(),
            'reviewed_at' => now(),
        ]);

        // Activate organization status
        if ($submission->organization) {
            $submission->organization->update(['status' => 'active']);
        }

        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'status_id' => $approvedStatus->id,
            'changed_by_user_id' => $admin->id,
            'notes' => 'Berkas pendaftaran dinyatakan LENGKAP dan DITERIMA oleh Admin ' . $admin->name,
        ]);

        ActivityLogger::log('approve_submission', "Menyetujui dan menerima berkas pengajuan #{$submission->tracking_code}", 'Submission', $submission->id);

        ActivityLogger::notifyUser(
            $submission->user_id,
            'submission_approved',
            'Berkas Pendaftaran DITERIMA & Disetujui!',
            "Selamat! Berkas pendaftaran #{$submission->tracking_code} dinyatakan LENGKAP dan DITERIMA oleh Badan Kesbangpol Kota Palembang.",
            route('user.history.detail', $submission->id)
        );

        return redirect()->back()->with('success', 'Berkas pendaftaran Ormas berhasil DITERIMA!');
    }

    public function reject(Request $request, Submission $submission)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10'],
        ], [
            'rejection_reason.required' => 'Alasan penolakan berkas wajib diisi.',
            'rejection_reason.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        $admin = Auth::user();
        $rejectedStatus = Status::where('code', Status::REJECTED)->firstOrFail();

        $submission->update([
            'status_id' => $rejectedStatus->id,
            'is_locked' => true,
            'completed_at' => now(),
            'reviewed_at' => now(),
        ]);

        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'status_id' => $rejectedStatus->id,
            'changed_by_user_id' => $admin->id,
            'notes' => 'Alasan Penolakan: ' . $request->input('rejection_reason'),
        ]);

        ActivityLogger::log('reject_submission', "Menolak berkas pengajuan #{$submission->tracking_code} dengan alasan: " . $request->input('rejection_reason'), 'Submission', $submission->id);

        ActivityLogger::notifyUser(
            $submission->user_id,
            'submission_rejected',
            'Pengajuan Berkas Ditolak',
            "Pengajuan #{$submission->tracking_code} telah ditolak. Alasan: {$request->input('rejection_reason')}",
            route('user.history.detail', $submission->id)
        );

        return redirect()->back()->with('success', 'Pengajuan berkas telah DITOLAK.');
    }

    public function assignAdmin(Request $request, Submission $submission)
    {
        $request->validate([
            'admin_id' => ['required', 'exists:users,id'],
        ]);

        $submission->update([
            'assigned_admin_id' => $request->input('admin_id'),
            'assigned_at' => now(),
        ]);

        $admin = User::find($request->input('admin_id'));

        ActivityLogger::log('assign_examiner', "Menugaskan Admin {$admin->name} untuk memeriksa pengajuan #{$submission->tracking_code}", 'Submission', $submission->id);

        ActivityLogger::notifyUser(
            $submission->user_id,
            'admin_assigned',
            'Petugas Verifikator Ditetapkan',
            "Pengajuan berkas #{$submission->tracking_code} Anda saat ini diperiksa oleh Admin {$admin->name}.",
            route('user.history.detail', $submission->id)
        );

        return redirect()->back()->with('success', "Pengajuan berhasil ditugaskan ke {$admin->name}.");
    }

    public function storeInternalNote(Request $request, Submission $submission)
    {
        $request->validate([
            'note' => ['required', 'string', 'min:3'],
        ]);

        AdminInternalNote::create([
            'target_type' => 'submission',
            'target_id' => $submission->id,
            'admin_id' => Auth::id(),
            'note' => $request->input('note'),
        ]);

        ActivityLogger::log('internal_note', "Menambahkan catatan internal pada pengajuan #{$submission->tracking_code}", 'Submission', $submission->id);

        return redirect()->back()->with('success', 'Catatan internal berhasil disimpan (Hanya dapat dilihat oleh Admin).');
    }

    public function downloadAll(Submission $submission)
    {
        $admin = Auth::user();
        $user = $submission->user;
        $orgName = $submission->organization->name ?? ($user->name ?? 'ormas');
        $cleanOrgName = Str::slug($orgName, '_');
        if (empty($cleanOrgName)) {
            $cleanOrgName = 'berkas_ormas_' . $submission->tracking_code;
        }

        $documents = SubmissionDocument::where('submission_id', $submission->id)->get();
        $orgForm = OrganizationForm::where('user_id', $user->id)->first();

        if ($documents->isEmpty() && !$orgForm) {
            return redirect()->back()->with('error', 'Belum ada berkas terupload yang dapat diunduh.');
        }

        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipFileName = $cleanOrgName . '_' . time() . '.zip';
        $zipPath = $tempDir . '/' . $zipFileName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        $reqTitles = [
            'req_1' => '01_Surat_Permohonan',
            'req_2' => '02_Akta_Pendirian',
            'req_3' => '03_AHU_Kemenkumham',
            'req_4' => '04_Program_Kerja',
            'req_5' => '05_Susunan_Pengurus_dan_SK',
            'req_6a' => '06a_Domisili_Keterangan_Lurah',
            'req_6b' => '06b_Domisili_Bukti_Kepemilikan',
            'req_7' => '07_NPWP_Ormas',
            'req_8' => '08_Surat_Pernyataan_Bebas_Sengketa',
            'req_9' => '09_Formulir_Isian_Data_Ormas',
            'req_10' => '10_Surat_Pernyataan_Kesanggupan_Lapor',
            'req_11' => '11_Rekom_Kemenag',
            'req_12' => '12_Rekom_Kebudayaan',
            'req_13' => '13_Surat_Pernyataan_Pejabat_Tokoh',
            'req_14a' => '14a_Biodata_Ketua',
            'req_14b' => '14b_Biodata_Sekretaris',
            'req_14c' => '14c_Biodata_Bendahara',
            'req_15a' => '15a_Pas_Foto_Ketua',
            'req_15b' => '15b_Pas_Foto_Sekretaris',
            'req_15c' => '15c_Pas_Foto_Bendahara',
            'req_16a' => '16a_KTP_Ketua',
            'req_16b' => '16b_KTP_Sekretaris',
            'req_16c' => '16c_KTP_Bendahara',
        ];

        // 1. Add all uploaded PDF documents
        foreach ($documents as $doc) {
            $filePath = null;
            if (Storage::disk('local')->exists($doc->file_path)) {
                $filePath = Storage::disk('local')->path($doc->file_path);
            } elseif (file_exists(storage_path('app/' . $doc->file_path))) {
                $filePath = storage_path('app/' . $doc->file_path);
            } elseif (file_exists(storage_path('app/private/' . $doc->file_path))) {
                $filePath = storage_path('app/private/' . $doc->file_path);
            }

            if ($filePath && file_exists($filePath)) {
                $ext = pathinfo($doc->original_name, PATHINFO_EXTENSION) ?: 'pdf';
                $prefixName = $reqTitles[$doc->document_key] ?? strtoupper($doc->document_key);
                $cleanOriginal = Str::slug(pathinfo($doc->original_name, PATHINFO_FILENAME), '_');
                $zipEntryName = $prefixName . '_' . $cleanOriginal . '.' . $ext;
                
                $zip->addFile($filePath, $zipEntryName);
            }
        }

        // 2. Add Excel file (Requirement No. 9)
        if ($orgForm) {
            if (!$orgForm->excel_path || !Storage::disk('local')->exists($orgForm->excel_path)) {
                ExcelFormGenerator::generate($orgForm);
                $orgForm->refresh();
            }

            $excelPath = null;
            if ($orgForm->excel_path) {
                if (Storage::disk('local')->exists($orgForm->excel_path)) {
                    $excelPath = Storage::disk('local')->path($orgForm->excel_path);
                } elseif (file_exists(storage_path('app/' . $orgForm->excel_path))) {
                    $excelPath = storage_path('app/' . $orgForm->excel_path);
                } elseif (file_exists(storage_path('app/private/' . $orgForm->excel_path))) {
                    $excelPath = storage_path('app/private/' . $orgForm->excel_path);
                }
            }

            if ($excelPath && file_exists($excelPath)) {
                $excelEntryName = '09_Formulir_Isian_Data_Ormas_' . $cleanOrgName . '.xlsx';
                $zip->addFile($excelPath, $excelEntryName);
            }
        }

        $zip->close();

        ActivityLogger::log('download_all_zip', "Mengunduh seluruh berkas ZIP untuk Ormas {$orgName} (Pengajuan #{$submission->tracking_code})", 'Submission', $submission->id);

        $downloadName = Str::slug($orgName, ' ') . '.zip';
        if (empty(trim(pathinfo($downloadName, PATHINFO_FILENAME)))) {
            $downloadName = 'berkas_ormas.zip';
        }

        return response()->download($zipPath, $downloadName)->deleteFileAfterSend(true);
    }
}
