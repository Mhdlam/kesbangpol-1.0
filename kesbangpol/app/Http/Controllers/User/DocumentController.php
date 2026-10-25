<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Models\SubmissionDocumentReview;
use App\Models\SubmissionDocumentVersion;
use App\Models\Status;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'document_key' => ['required', 'string'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20MB max
            'phone' => ['nullable', 'string', 'max:20'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
        ], [
            'document.required' => 'File dokumen wajib dipilih.',
            'document.mimes' => 'Format file harus berupa PDF.',
            'document.max' => 'Ukuran file PDF maksimal 20 MB.',
        ]);

        $user = Auth::user();
        $docKey = $request->input('document_key');

        // Check submission lock status (Requirement Y)
        $submission = Submission::where('user_id', $user->id)
            ->latest()
            ->first();

        // An approved submission is historical; new uploads belong to a fresh submission.
        if ($submission && $submission->status && $submission->status->code === Status::APPROVED) {
            $submission = null;
        }

        if ($submission && $submission->is_locked) {
            // Check if this specific document is requested for revision
            $docReview = SubmissionDocumentReview::where('submission_id', $submission->id)
                ->where('document_key', $docKey)
                ->first();

            // Fallback to parent key if docKey is sub-item (e.g. req_14a -> req_14)
            if (!$docReview) {
                $parentKey = preg_replace('/[a-z]$/', '', $docKey);
                if ($parentKey !== $docKey) {
                    $docReview = SubmissionDocumentReview::where('submission_id', $submission->id)
                        ->where('document_key', $parentKey)
                        ->first();
                }
            }

            $isAllowedForRevision = ($submission->status && $submission->status->code === Status::REVISION_NEEDED) 
                && ($docReview && in_array($docReview->status, ['revision_needed', 'rejected', 'pending']));

            // If no specific review was tagged on this key, check if document is NOT approved
            if (!$isAllowedForRevision && ($submission->status && $submission->status->code === Status::REVISION_NEEDED)) {
                $isApproved = $docReview && $docReview->status === 'approved';
                if (!$isApproved) {
                    $isAllowedForRevision = true;
                }
            }

            if (!$isAllowedForRevision) {
                return redirect()->back()->with('error', 'Dokumen ini telah disetujui atau pengajuan Anda sedang dikunci untuk pemeriksaan. Anda hanya dapat memperbarui dokumen yang diminta revisi atau ditolak oleh Admin.');
            }
        }

        // Check if req_16 sub items require phone & whatsapp
        if (in_array($docKey, ['req_16a', 'req_16b', 'req_16c'])) {
            $request->validate([
                'phone' => ['required', 'string', 'max:20'],
                'whatsapp' => ['required', 'string', 'max:20'],
            ], [
                'phone.required' => 'Nomor Handphone wajib diisi.',
                'whatsapp.required' => 'Nomor WhatsApp aktif wajib diisi.',
            ]);
        }

        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('documents/' . $user->id, $fileName, 'local');

        $metadata = [];
        if ($request->filled('phone')) {
            $metadata['phone'] = $request->input('phone');
        }
        if ($request->filled('whatsapp')) {
            $metadata['whatsapp'] = $request->input('whatsapp');
        }

        // Check existing document and store version history (Requirement H)
        $existingQuery = SubmissionDocument::where('user_id', $user->id)
            ->where('document_key', $docKey);

        if ($submission) {
            $existingQuery->where('submission_id', $submission->id);
        } else {
            $existingQuery->whereNull('submission_id');
        }

        $existing = $existingQuery->first();

        if ($existing) {
            $currentVersionCount = SubmissionDocumentVersion::where('user_id', $user->id)
                ->where('document_key', $docKey)
                ->count();

            $nextVersion = $currentVersionCount + 1;

            // Preserve old version in history table before updating
            SubmissionDocumentVersion::create([
                'submission_id' => $submission ? $submission->id : ($existing->submission_id ?? null),
                'document_key' => $docKey,
                'user_id' => $user->id,
                'version' => $nextVersion,
                'original_name' => $existing->original_name,
                'file_path' => $existing->file_path,
                'file_size' => $existing->file_size,
                'file_mime' => $existing->file_mime,
            ]);

            // Delete old record to replace with new document model
            $existing->delete();
        }

        $newDoc = SubmissionDocument::create([
            'submission_id' => $submission ? $submission->id : null,
            'user_id' => $user->id,
            'document_key' => $docKey,
            'original_name' => $originalName,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'file_mime' => $file->getClientMimeType() ?: 'application/pdf',
            'metadata' => !empty($metadata) ? $metadata : null,
        ]);

        // If revision was requested, reset this document review status to pending to signal re-upload
        if ($submission && $submission->status && $submission->status->code === Status::REVISION_NEEDED) {
            SubmissionDocumentReview::updateOrCreate(
                ['submission_id' => $submission->id, 'document_key' => $docKey],
                ['status' => 'pending', 'admin_id' => Auth::id() ?: ($submission->assigned_admin_id ?: 1)]
            );

            ActivityLogger::notifyAdmins(
                'revision_submitted',
                'Dokumen Revisi Diperbarui',
                "Ormas {$user->name} memperbarui dokumen " . $originalName . " untuk pengajuan #{$submission->tracking_code}.",
                route('admin.submissions.show', $submission->id)
            );

            ActivityLogger::log('revision_updated', "Memperbarui dokumen {$docKey} untuk revisi pengajuan #{$submission->tracking_code}", 'Submission', $submission->id);
        }

        ActivityLogger::log('upload_document', "Mengunggah dokumen '{$originalName}' [{$docKey}] oleh Ormas {$user->name}", 'SubmissionDocument', $newDoc->id, $user);

        return redirect()->back()->with('success', 'Dokumen ' . $originalName . ' berhasil diunggah.');
    }

    public function download(SubmissionDocument $document)
    {
        $user = Auth::user();

        // Authorization check: User can only access their own docs, admin can access all
        if ($user->role !== 'admin' && $document->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke dokumen ini.');
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'File dokumen tidak ditemukan.');
        }

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function delete(SubmissionDocument $document)
    {
        $user = Auth::user();

        if ($user->role !== 'admin' && $document->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus dokumen ini.');
        }

        $submission = $document->submission;

        // Draft uploads have no submission yet. Keep them locked only while a newer
        // submission is under review; an approved submission must not lock new drafts.
        if ($user->role !== 'admin' && !$submission) {
            $latestSubmission = Submission::where('user_id', $user->id)->latest()->first();
            if ($latestSubmission && $latestSubmission->is_locked
                && (!$latestSubmission->status || $latestSubmission->status->code !== Status::APPROVED)) {
                $submission = $latestSubmission;
            }
        }

        if ($user->role !== 'admin' && $submission && $submission->is_locked) {
            $isRevision = ($submission->status && $submission->status->code === Status::REVISION_NEEDED);
            $docReview = SubmissionDocumentReview::where('submission_id', $submission->id)
                ->where('document_key', $document->document_key)
                ->first();

            $isApproved = $docReview && $docReview->status === 'approved';

            if (!$isRevision || $isApproved) {
                return redirect()->back()->with('error', 'Dokumen ini telah disetujui atau pengajuan sedang dikunci. Dokumen tidak dapat dihapus.');
            }
        }

        $docName = $document->original_name;
        $docKey = $document->document_key;

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        ActivityLogger::log('delete_document', "Menghapus dokumen '{$docName}' [{$docKey}] oleh Ormas {$user->name}", 'SubmissionDocument', $document->id, $user);

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }

    public function deleteAll(Request $request)
    {
        $user = Auth::user();

        $submission = Submission::where('user_id', $user->id)->latest()->first();
        if ($user->role !== 'admin' && $submission && $submission->is_locked
            && (!$submission->status || $submission->status->code !== Status::APPROVED)) {
            return redirect()->back()->with('error', 'Pengajuan sedang dikunci atau dalam proses revisi. Berkas yang disetujui tidak dapat dihapus sekaligus.');
        }

        $documentsQuery = SubmissionDocument::where('user_id', $user->id);
        if ($user->role !== 'admin') {
            $documentsQuery->whereNull('submission_id');
        }
        $documents = $documentsQuery->get();

        if ($documents->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada berkas terupload yang dapat dihapus.');
        }

        $count = $documents->count();

        foreach ($documents as $doc) {
            if (Storage::disk('local')->exists($doc->file_path)) {
                Storage::disk('local')->delete($doc->file_path);
            }
            $doc->delete();
        }

        ActivityLogger::log('delete_all_documents', "Menghapus seluruh {$count} berkas terupload oleh Ormas {$user->name}", 'SubmissionDocument', null, $user);

        return redirect()->back()->with('success', "Seluruh {$count} berkas terupload berhasil dihapus.");
    }
}
