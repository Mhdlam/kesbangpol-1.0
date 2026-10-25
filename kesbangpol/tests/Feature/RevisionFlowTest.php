<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationForm;
use App\Models\Status;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Models\SubmissionDocumentReview;
use App\Models\SubmissionDocumentVersion;
use App\Models\SubmissionHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RevisionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed statuses if not present
        Status::firstOrCreate(['code' => Status::SUBMITTED], ['name' => 'Diajukan', 'badge_color' => 'sky']);
        Status::firstOrCreate(['code' => Status::IN_PROCESS], ['name' => 'Sedang Diproses', 'badge_color' => 'blue']);
        Status::firstOrCreate(['code' => Status::REVISION_NEEDED], ['name' => 'Perlu Direvisi', 'badge_color' => 'amber']);
        Status::firstOrCreate(['code' => Status::APPROVED], ['name' => 'Disetujui', 'badge_color' => 'emerald']);
        Status::firstOrCreate(['code' => Status::REJECTED], ['name' => 'Ditolak', 'badge_color' => 'rose']);
    }

    public function test_admin_submissions_index_does_not_have_checkboxes_or_bulk_action_bar(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'login_id' => '999001']);
        $user = User::factory()->create(['role' => 'user', 'login_id' => '100001']);

        $submittedStatus = Status::where('code', Status::SUBMITTED)->first();
        Submission::create([
            'user_id' => $user->id,
            'status_id' => $submittedStatus->id,
            'title' => 'Pengajuan Test Ormas',
            'tracking_code' => 'KSB-TEST-001',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.submissions.index'));

        $response->assertStatus(200);
        // Bulk action bar elements must be removed
        $response->assertDontSee('Berkas Dipilih');
        $response->assertDontSee('Pilih Admin');
        $response->assertDontSee('Tugaskan Admin');
        $response->assertDontSee('Export Data CSV');
        // Checkboxes must be removed
        $response->assertDontSee('name="ids[]"', false);
    }

    public function test_user_history_detail_shows_revision_information_and_revisi_sekarang_button(): void
    {
        $user = User::factory()->create(['role' => 'user', 'login_id' => '100002']);
        $admin = User::factory()->create(['role' => 'admin', 'login_id' => '999002']);

        $org = Organization::create([
            'user_id' => $user->id,
            'name' => 'Ormas Maju Bersama',
            'is_religious' => false,
        ]);

        $revisionStatus = Status::where('code', Status::REVISION_NEEDED)->first();

        $submission = Submission::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'status_id' => $revisionStatus->id,
            'assigned_admin_id' => $admin->id,
            'title' => 'Pengajuan Pendaftaran Ormas Maju Bersama',
            'tracking_code' => 'KSB-REV-001',
            'revision_count' => 1,
            'submitted_at' => now()->subDays(2),
            'reviewed_at' => now()->subDay(),
            'is_locked' => true,
        ]);

        // Documents
        $doc1 = SubmissionDocument::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'document_key' => 'req_1',
            'original_name' => 'surat_permohonan.pdf',
            'file_path' => 'documents/test/surat_permohonan.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
        ]);

        $doc2 = SubmissionDocument::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'document_key' => 'req_2',
            'original_name' => 'akta_notaris.pdf',
            'file_path' => 'documents/test/akta_notaris.pdf',
            'file_size' => 2048,
            'file_mime' => 'application/pdf',
        ]);

        // Reviews: req_1 approved, req_2 revision_needed
        SubmissionDocumentReview::create([
            'submission_id' => $submission->id,
            'document_key' => 'req_1',
            'admin_id' => $admin->id,
            'status' => 'approved',
            'notes' => 'Sesuai dan lengkap',
        ]);

        SubmissionDocumentReview::create([
            'submission_id' => $submission->id,
            'document_key' => 'req_2',
            'admin_id' => $admin->id,
            'status' => 'revision_needed',
            'notes' => 'Akta notaris buram pada pasal 5, tolong scan ulang.',
        ]);

        SubmissionHistory::create([
            'submission_id' => $submission->id,
            'status_id' => $revisionStatus->id,
            'changed_by_user_id' => $admin->id,
            'notes' => 'Catatan Revisi dari Admin: Mohon perbaiki akta notaris yang buram.',
        ]);

        $response = $this->actingAs($user)->get(route('user.history.detail', $submission->id));

        $response->assertStatus(200);
        $response->assertSee('Instruksi Revisi Berkas Pengajuan');
        $response->assertSee('Revisi Sekarang');
        $response->assertSee('Mohon perbaiki akta notaris yang buram.');
        $response->assertSee('Akta notaris buram pada pasal 5, tolong scan ulang.');
        $response->assertSee('Surat Permohonan');
        $response->assertSee('Disetujui');
    }

    public function test_start_revision_preserves_approved_documents_and_removes_problematic_documents(): void
    {
        $user = User::factory()->create(['role' => 'user', 'login_id' => '100003']);
        $admin = User::factory()->create(['role' => 'admin', 'login_id' => '999003']);

        $org = Organization::create([
            'user_id' => $user->id,
            'name' => 'Ormas Sejahtera',
        ]);

        $revisionStatus = Status::where('code', Status::REVISION_NEEDED)->first();

        $submission = Submission::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'status_id' => $revisionStatus->id,
            'assigned_admin_id' => $admin->id,
            'title' => 'Pengajuan Ormas Sejahtera',
            'tracking_code' => 'KSB-SEJ-001',
            'revision_count' => 1,
            'is_locked' => true,
        ]);

        $approvedDoc = SubmissionDocument::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'document_key' => 'req_1',
            'original_name' => 'approved_file.pdf',
            'file_path' => 'documents/test/approved_file.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
        ]);

        $rejectedDoc = SubmissionDocument::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'document_key' => 'req_2',
            'original_name' => 'problematic_file.pdf',
            'file_path' => 'documents/test/problematic_file.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
        ]);

        SubmissionDocumentReview::create([
            'submission_id' => $submission->id,
            'document_key' => 'req_1',
            'admin_id' => $admin->id,
            'status' => 'approved',
        ]);

        SubmissionDocumentReview::create([
            'submission_id' => $submission->id,
            'document_key' => 'req_2',
            'admin_id' => $admin->id,
            'status' => 'revision_needed',
            'notes' => 'Tolong scan ulang lembar pengesahan',
        ]);

        // Start revision
        $response = $this->actingAs($user)->post(route('user.submission.start_revision', $submission->id));

        $response->assertRedirect(route('user.dashboard'));

        // Assert approved document IS PRESERVED
        $this->assertDatabaseHas('submission_documents', [
            'id' => $approvedDoc->id,
            'document_key' => 'req_1',
            'original_name' => 'approved_file.pdf',
        ]);

        // Assert problematic document is removed from active documents
        $this->assertDatabaseMissing('submission_documents', [
            'id' => $rejectedDoc->id,
            'document_key' => 'req_2',
        ]);

        // Assert version was created for rejected doc
        $this->assertDatabaseHas('submission_document_versions', [
            'submission_id' => $submission->id,
            'document_key' => 'req_2',
            'original_name' => 'problematic_file.pdf',
        ]);
    }

    public function test_dashboard_renders_in_revision_mode_correctly(): void
    {
        $user = User::factory()->create(['role' => 'user', 'login_id' => '100004']);
        $admin = User::factory()->create(['role' => 'admin', 'login_id' => '999004']);

        $org = Organization::create([
            'user_id' => $user->id,
            'name' => 'Ormas Bersatu',
        ]);

        $revisionStatus = Status::where('code', Status::REVISION_NEEDED)->first();

        $submission = Submission::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'status_id' => $revisionStatus->id,
            'assigned_admin_id' => $admin->id,
            'title' => 'Pengajuan Ormas Bersatu',
            'tracking_code' => 'KSB-BST-001',
            'revision_count' => 1,
            'is_locked' => true,
        ]);

        SubmissionDocument::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'document_key' => 'req_1',
            'original_name' => 'surat_permohonan.pdf',
            'file_path' => 'documents/test/surat_permohonan.pdf',
            'file_size' => 1024,
            'file_mime' => 'application/pdf',
        ]);

        SubmissionDocumentReview::create([
            'submission_id' => $submission->id,
            'document_key' => 'req_1',
            'admin_id' => $admin->id,
            'status' => 'approved',
        ]);

        SubmissionDocumentReview::create([
            'submission_id' => $submission->id,
            'document_key' => 'req_2',
            'admin_id' => $admin->id,
            'status' => 'revision_needed',
            'notes' => 'Nomor akta belum dicantumkan',
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Mode Revisi Pengajuan');
        $response->assertSee('KSB-BST-001');
        // Delete all button must not be visible in revision mode
        $response->assertDontSee('Hapus Semua Berkas Terupload');
        // Approved doc is marked approved
        $response->assertSee('DISETUJUI ADMIN');
        // Problematic doc is marked needing revision
        $response->assertSee('PERLU REVISI');
        $response->assertSee('Nomor akta belum dicantumkan');
    }

    public function test_submitting_revision_updates_existing_submission_without_creating_new_one(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'user', 'login_id' => '100005']);
        $admin = User::factory()->create(['role' => 'admin', 'login_id' => '999005']);

        $org = Organization::create([
            'user_id' => $user->id,
            'name' => 'Ormas Bangkit',
            'is_religious' => false,
        ]);

        $revisionStatus = Status::where('code', Status::REVISION_NEEDED)->first();

        $submission = Submission::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'status_id' => $revisionStatus->id,
            'assigned_admin_id' => $admin->id,
            'title' => 'Pengajuan Ormas Bangkit',
            'tracking_code' => 'KSB-BGT-001',
            'revision_count' => 1,
            'is_locked' => true,
        ]);

        // Complete form 9
        OrganizationForm::create([
            'user_id' => $user->id,
            'organization_id' => $org->id,
            'is_confirmed' => true,
            'nama_ormas' => 'Ormas Bangkit',
        ]);

        // Create documents for 1-16 (excluding 9, 11-13 optional/non-religious)
        $keys = [
            'req_1', 'req_2', 'req_3', 'req_4', 'req_5',
            'req_6a', 'req_6b', 'req_7', 'req_8', 'req_10',
            'req_14a', 'req_14b', 'req_14c',
            'req_15a', 'req_15b', 'req_15c',
            'req_16a', 'req_16b', 'req_16c'
        ];

        foreach ($keys as $k) {
            SubmissionDocument::create([
                'submission_id' => $submission->id,
                'user_id' => $user->id,
                'document_key' => $k,
                'original_name' => "{$k}.pdf",
                'file_path' => "documents/test/{$k}.pdf",
                'file_size' => 1024,
                'file_mime' => 'application/pdf',
            ]);
        }

        // Initially 1 submission exists
        $this->assertEquals(1, Submission::count());

        // User submits revision
        $response = $this->actingAs($user)->post(route('user.submission.send'));

        $response->assertRedirect(route('user.history.detail', $submission->id));

        // CRITICAL ASSERTION: Still exactly 1 submission in DB (NO new submission created!)
        $this->assertEquals(1, Submission::count());

        $submission->refresh();
        $inProcessStatus = Status::where('code', Status::IN_PROCESS)->first();
        $this->assertEquals($inProcessStatus->id, $submission->status_id);

        // Check history log created for revision
        $this->assertDatabaseHas('submission_histories', [
            'submission_id' => $submission->id,
            'status_id' => $inProcessStatus->id,
            'notes' => 'Ormas telah mengirimkan perbaikan dokumen revisi (Revisi #1). Berkas sedang diproses kembali oleh Kesbangpol.',
        ]);
    }
}
