<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Status;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Models\SubmissionDocumentReview;
use App\Models\SubmissionHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $submittedSt = Status::where('code', Status::SUBMITTED)->first();
        $inProcessSt = Status::where('code', Status::IN_PROCESS)->first();
        $revisionSt = Status::where('code', Status::REVISION_NEEDED)->first();
        $approvedSt = Status::where('code', Status::APPROVED)->first();
        $rejectedSt = Status::where('code', Status::REJECTED)->first();

        $admin = User::where('role', 'admin')->first();

        // 1. Ormas Demo 1 - Baru (Belum diperiksa)
        $user1 = User::updateOrCreate(
            ['email' => 'gmni@palembang.org'],
            [
                'name' => 'Rahmat Wijaya (GMNI)',
                'login_id' => '112233',
                'role' => 'user',
                'password' => Hash::make('password123'),
                'plain_password' => 'password123',
                'phone_wa' => '081122334455',
                'university' => 'Universitas Sriwijaya',
                'bio' => 'Ketua GMNI Palembang',
            ]
        );

        $org1 = Organization::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'name' => 'Gerakan Mahasiswa Nasional Indonesia (GMNI)',
                'abbreviation' => 'GMNI',
                'registration_number' => 'REG/GMNI/2026/001',
                'university' => 'Universitas Sriwijaya',
                'address' => 'Jl. Merdeka No. 45, Kec. Bukit Kecil, Palembang',
                'district' => 'Bukit Kecil',
                'category' => 'Kepemudaan',
                'leader_name' => 'Rahmat Wijaya',
                'secretary_name' => 'Siti Nurhaliza',
                'treasurer_name' => 'Doni Kusuma',
                'is_religious' => false,
                'status' => 'active',
            ]
        );

        $sub1 = Submission::updateOrCreate(
            ['tracking_code' => 'ORMAS-2026-001'],
            [
                'user_id' => $user1->id,
                'organization_id' => $org1->id,
                'status_id' => $submittedSt->id,
                'assigned_admin_id' => $admin ? $admin->id : null,
                'title' => 'Pengajuan Pendaftaran Ormas GMNI Palembang',
                'district' => 'Bukit Kecil',
                'submitted_at' => now(),
            ]
        );

        // Upload dummy docs
        foreach (['req_1', 'req_2', 'req_3', 'req_4', 'req_5', 'req_6a', 'req_6b', 'req_7', 'req_8', 'req_10', 'req_14a', 'req_14b', 'req_14c', 'req_15a', 'req_15b', 'req_15c', 'req_16a', 'req_16b', 'req_16c'] as $key) {
            SubmissionDocument::updateOrCreate(
                ['submission_id' => $sub1->id, 'document_key' => $key],
                [
                    'user_id' => $user1->id,
                    'original_name' => "Dokumen_{$key}_GMNI.pdf",
                    'file_path' => "documents/{$user1->id}/sample_{$key}.pdf",
                    'file_size' => 1024000,
                    'file_mime' => 'application/pdf',
                ]
            );
        }

        // 2. Ormas Demo 2 - Perlu Revisi
        $user2 = User::updateOrCreate(
            ['email' => 'pmkii@palembang.org'],
            [
                'name' => 'Dewi Lestari (PMKRI)',
                'login_id' => '445566',
                'role' => 'user',
                'password' => Hash::make('password123'),
                'plain_password' => 'password123',
                'phone_wa' => '082233445566',
                'university' => 'Universitas Katolik Musi Charitas',
                'bio' => 'Ketua PMKRI Palembang',
            ]
        );

        $org2 = Organization::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'name' => 'Perhimpunan Mahasiswa Katolik Republik Indonesia (PMKRI)',
                'abbreviation' => 'PMKRI',
                'registration_number' => 'REG/PMKRI/2026/002',
                'university' => 'Universitas Katolik Musi Charitas',
                'address' => 'Jl. Veteran No. 88, Kec. Ilir Timur II, Palembang',
                'district' => 'Ilir Timur II',
                'category' => 'Keagamaan & Kepemudaan',
                'leader_name' => 'Dewi Lestari',
                'secretary_name' => 'Antonius Subianto',
                'treasurer_name' => 'Maria Fransiska',
                'is_religious' => true,
                'status' => 'active',
            ]
        );

        $sub2 = Submission::updateOrCreate(
            ['tracking_code' => 'ORMAS-2026-002'],
            [
                'user_id' => $user2->id,
                'organization_id' => $org2->id,
                'status_id' => $revisionSt->id,
                'assigned_admin_id' => $admin ? $admin->id : null,
                'title' => 'Pengajuan Pendaftaran PMKRI Cabang Palembang',
                'district' => 'Ilir Timur II',
                'revision_count' => 1,
                'is_locked' => true,
                'submitted_at' => now()->subDays(3),
                'reviewed_at' => now()->subDay(),
            ]
        );

        SubmissionDocumentReview::updateOrCreate(
            ['submission_id' => $sub2->id, 'document_key' => 'req_5'],
            [
                'admin_id' => $admin ? $admin->id : 1,
                'status' => 'revision_needed',
                'notes' => 'Mohon upload SK kepengurusan terbaru dengan tanda tangan basah.',
            ]
        );

        SubmissionHistory::updateOrCreate(
            ['submission_id' => $sub2->id, 'notes' => 'Catatan Revisi dari Admin: Mohon upload SK kepengurusan terbaru dengan tanda tangan basah.'],
            [
                'status_id' => $revisionSt->id,
                'changed_by_user_id' => $admin ? $admin->id : 1,
            ]
        );
    }
}
