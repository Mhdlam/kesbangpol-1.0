<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Models\SubmissionDocumentReview;
use App\Models\SubmissionDocumentVersion;
use App\Services\RequirementChecker;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Run automatic backend cleanup for submissions older than 30 days
        Submission::cleanupExpiredHistory();

        $submissions = Submission::where('user_id', $user->id)
            ->with(['status', 'histories.status', 'histories.changedBy', 'documents'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.history', compact('submissions'));
    }

    public function show(Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak diizinkan mengakses pengajuan ini.');
        }

        $submission->load([
            'status', 
            'histories.status', 
            'histories.changedBy', 
            'documents', 
            'organization',
            'documentReviews.admin',
            'assignedAdmin'
        ]);

        $reqCheck = RequirementChecker::checkUserStatus($user, $submission);

        $revisionStatus = Status::where('code', Status::REVISION_NEEDED)->first();
        $latestRevisionHistory = $submission->histories
            ->where('status_id', $revisionStatus?->id)
            ->first();

        return view('user.history_detail', compact('submission', 'reqCheck', 'latestRevisionHistory'));
    }

    public function startRevision(Submission $submission)
    {
        $user = Auth::user();

        if ($submission->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!$submission->status || $submission->status->code !== Status::REVISION_NEEDED) {
            return redirect()->route('user.history.detail', $submission->id)
                ->with('error', 'Pengajuan ini tidak sedang dalam status revisi.');
        }

        // 1. Fetch all document reviews for this submission
        $reviews = SubmissionDocumentReview::where('submission_id', $submission->id)->get();

        // 2. Identify keys that need revision or were rejected
        $problematicKeys = [];
        foreach ($reviews as $rev) {
            if (in_array($rev->status, ['revision_needed', 'rejected'])) {
                $problematicKeys[] = $rev->document_key;

                // Expand parent keys to children
                if ($rev->document_key === 'req_6') {
                    $problematicKeys[] = 'req_6a';
                    $problematicKeys[] = 'req_6b';
                } elseif ($rev->document_key === 'req_14') {
                    $problematicKeys[] = 'req_14a';
                    $problematicKeys[] = 'req_14b';
                    $problematicKeys[] = 'req_14c';
                } elseif ($rev->document_key === 'req_15') {
                    $problematicKeys[] = 'req_15a';
                    $problematicKeys[] = 'req_15b';
                    $problematicKeys[] = 'req_15c';
                } elseif ($rev->document_key === 'req_16') {
                    $problematicKeys[] = 'req_16a';
                    $problematicKeys[] = 'req_16b';
                    $problematicKeys[] = 'req_16c';
                }
            }
        }

        // If admin did not flag specific keys, identify any documents that are not approved
        if (empty($problematicKeys)) {
            $approvedKeys = SubmissionDocumentReview::where('submission_id', $submission->id)
                ->where('status', 'approved')
                ->pluck('document_key')
                ->toArray();

            $userDocs = SubmissionDocument::where('user_id', $user->id)->get();
            foreach ($userDocs as $doc) {
                if (!in_array($doc->document_key, $approvedKeys)) {
                    $problematicKeys[] = $doc->document_key;
                }
            }
        }

        $problematicKeys = array_unique($problematicKeys);

        // 3. Archive problematic documents into document versions and delete active record so user can re-upload
        $docsToRemove = SubmissionDocument::where('user_id', $user->id)
            ->whereIn('document_key', $problematicKeys)
            ->get();

        foreach ($docsToRemove as $doc) {
            $hasVersion = SubmissionDocumentVersion::where('user_id', $user->id)
                ->where('document_key', $doc->document_key)
                ->where('file_path', $doc->file_path)
                ->exists();

            if (!$hasVersion) {
                $count = SubmissionDocumentVersion::where('user_id', $user->id)
                    ->where('document_key', $doc->document_key)
                    ->count();

                SubmissionDocumentVersion::create([
                    'submission_id' => $submission->id,
                    'document_key' => $doc->document_key,
                    'user_id' => $user->id,
                    'version' => $count + 1,
                    'original_name' => $doc->original_name,
                    'file_path' => $doc->file_path,
                    'file_size' => $doc->file_size,
                    'file_mime' => $doc->file_mime,
                ]);
            }

            // Remove active document record so user is prompted to upload fresh replacement
            $doc->delete();
        }

        return redirect()->route('user.dashboard')
            ->with('success', 'Mode revisi aktif untuk Pengajuan #' . $submission->tracking_code . '. Berkas yang disetujui tetap tersimpan dengan aman. Silakan unggah ulang berkas yang ditolak/perlu direvisi.');
    }
}
