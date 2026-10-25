<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Models\SubmissionDocumentReview;
use App\Models\SubmissionHistory;
use App\Services\RequirementChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    public function submit(Request $request)
    {
        $user = Auth::user();

        // Check active submission
        $activeSubmission = Submission::where('user_id', $user->id)
            ->latest()
            ->first();

        $isRevision = $activeSubmission && $activeSubmission->status && $activeSubmission->status->code === Status::REVISION_NEEDED;

        $statusCheck = RequirementChecker::checkUserStatus($user, $isRevision ? $activeSubmission : null);

        if (! $statusCheck['is_fully_complete']) {
            $msg = $isRevision
                ? 'Semua berkas revisi yang diminta harus dilengkapi terlebih dahulu sebelum mengirim ulang revisi.'
                : 'Semua persyaratan wajib harus dilengkapi terlebih dahulu sebelum mengirim berkas.';

            return redirect()->route('user.dashboard')
                ->with('error', $msg);
        }

        $inProcessStatus = Status::where('code', Status::IN_PROCESS)->first();

        if ($isRevision) {
            // ==========================================
            // REVISION SUBMISSION: KEEP EXISTING SUBMISSION
            // ==========================================
            
            // 1. Associate newly uploaded replacement documents to the existing submission
            SubmissionDocument::where('user_id', $user->id)
                ->whereNull('submission_id')
                ->update(['submission_id' => $activeSubmission->id]);

            // 2. Reset review status of revised documents to pending for re-examination
            SubmissionDocumentReview::where('submission_id', $activeSubmission->id)
                ->whereIn('status', ['revision_needed', 'rejected'])
                ->update(['status' => 'pending']);

            // 3. Update active submission status
            $activeSubmission->update([
                'status_id' => $inProcessStatus->id,
                'is_locked' => true,
                'reviewed_at' => null,
                'updated_at' => now(),
            ]);

            // 4. Create submission history record
            SubmissionHistory::create([
                'submission_id' => $activeSubmission->id,
                'status_id' => $inProcessStatus->id,
                'changed_by_user_id' => $user->id,
                'notes' => 'Ormas telah mengirimkan perbaikan dokumen revisi (Revisi #' . $activeSubmission->revision_count . '). Berkas sedang diproses kembali oleh Kesbangpol.',
            ]);

            // 5. Activity log & notify admins
            \App\Services\ActivityLogger::log(
                'send_revision', 
                "Mengirimkan perbaikan berkas revisi pengajuan #{$activeSubmission->tracking_code} oleh {$user->name}", 
                'Submission', 
                $activeSubmission->id, 
                $user
            );

            \App\Services\ActivityLogger::notifyAdmins(
                'revision_submitted',
                'Berkas Revisi Masuk',
                "Ormas {$user->name} telah mengunggah berkas perbaikan untuk pengajuan #{$activeSubmission->tracking_code}.",
                route('admin.submissions.show', $activeSubmission->id)
            );

            return redirect()->route('user.history.detail', $activeSubmission->id)
                ->with('success', 'Berkas revisi berhasil dikirim! Seluruh berkas yang telah disetujui sebelumnya tetap tersimpan dan pengajuan Anda sedang diperiksa kembali oleh Admin Kesbangpol.');
        }

        // ==========================================
        // FRESH SUBMISSION: CREATE NEW SUBMISSION
        // ==========================================
        $org = $user->organization;
        $trackingCode = 'KSB-ORMAS-' . strtoupper(Str::random(6));

        $submission = Submission::create([
            'user_id' => $user->id,
            'organization_id' => $org?->id,
            'status_id' => $inProcessStatus->id,
            'title' => 'Pengajuan Pendaftaran Berkas Ormas - ' . ($org->name ?? $user->name),
            'tracking_code' => $trackingCode,
            'submitted_at' => now(),
        ]);

        // Associate user documents to this submission
        SubmissionDocument::where('user_id', $user->id)
            ->whereNull('submission_id')
            ->update(['submission_id' => $submission->id]);

        // Create submission history log
        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'status_id' => $inProcessStatus->id,
            'changed_by_user_id' => $user->id,
            'notes' => 'Pengajuan berkas telah dikirimkan oleh pemohon dan sedang diproses oleh Kesbangpol Kota Palembang.',
        ]);

        \App\Services\ActivityLogger::log('send_submission', "Mengirimkan berkas pendaftaran Ormas ke Kesbangpol (Tracking: #{$trackingCode}) oleh {$user->name}", 'Submission', $submission->id, $user);

        return redirect()->route('user.history')
            ->with('success', 'Berkas berhasil dikirim! Status pengajuan Anda saat ini: Berkas sedang diproses Kesbangpol.');
    }
}
