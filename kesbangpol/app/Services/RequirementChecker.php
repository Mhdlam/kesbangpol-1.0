<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\OrganizationForm;
use App\Models\Submission;
use App\Models\SubmissionDocument;
use App\Models\SubmissionDocumentReview;
use App\Models\User;

class RequirementChecker
{
    public static function checkUserStatus(User $user, ?Submission $submission = null): array
    {
        $docQuery = SubmissionDocument::where('user_id', $user->id);
        if ($submission) {
            $docQuery->where(function ($q) use ($submission) {
                $q->where('submission_id', $submission->id)
                  ->orWhereNull('submission_id');
            });
        }
        $docs = $docQuery->get()->keyBy('document_key');

        $reviews = [];
        if ($submission) {
            $reviews = SubmissionDocumentReview::where('submission_id', $submission->id)
                ->get()
                ->keyBy('document_key');
        }

        $org = $user->organization ?? Organization::firstOrCreate(
            ['user_id' => $user->id],
            ['name' => 'Organisasi ' . $user->name]
        );

        $orgForm = OrganizationForm::where('user_id', $user->id)->first();

        $getReviewInfo = function ($key) use ($reviews) {
            if (isset($reviews[$key])) {
                return [
                    'status' => $reviews[$key]->status, // approved, revision_needed, rejected, pending, not_applicable
                    'notes' => $reviews[$key]->notes,
                    'reviewed_at' => $reviews[$key]->updated_at,
                ];
            }

            // Fallback to parent key (e.g. req_6 for req_6a, req_14 for req_14a)
            $parentKey = preg_replace('/[a-z]$/', '', $key);
            if ($parentKey !== $key && isset($reviews[$parentKey])) {
                return [
                    'status' => $reviews[$parentKey]->status,
                    'notes' => $reviews[$parentKey]->notes,
                    'reviewed_at' => $reviews[$parentKey]->updated_at,
                ];
            }

            // Fallback if key is parent (e.g. req_6, req_14) and child has revision/rejection
            $childKeys = match ($key) {
                'req_6' => ['req_6a', 'req_6b'],
                'req_14' => ['req_14a', 'req_14b', 'req_14c'],
                'req_15' => ['req_15a', 'req_15b', 'req_15c'],
                'req_16' => ['req_16a', 'req_16b', 'req_16c'],
                default => [],
            };

            if (!empty($childKeys)) {
                foreach ($childKeys as $ck) {
                    if (isset($reviews[$ck]) && in_array($reviews[$ck]->status, ['revision_needed', 'rejected'])) {
                        return [
                            'status' => $reviews[$ck]->status,
                            'notes' => $reviews[$ck]->notes,
                            'reviewed_at' => $reviews[$ck]->updated_at,
                        ];
                    }
                }
                $allApproved = true;
                foreach ($childKeys as $ck) {
                    if (!isset($reviews[$ck]) || $reviews[$ck]->status !== 'approved') {
                        $allApproved = false;
                        break;
                    }
                }
                if ($allApproved && !empty($childKeys)) {
                    return [
                        'status' => 'approved',
                        'notes' => null,
                        'reviewed_at' => null,
                    ];
                }
            }

            return [
                'status' => 'pending',
                'notes' => null,
                'reviewed_at' => null,
            ];
        };

        $requirements = [
            1 => [
                'key' => 'req_1',
                'title' => 'Surat Permohonan',
                'type' => 'wajib',
                'completed' => isset($docs['req_1']),
                'missing' => !isset($docs['req_1']),
                'file' => $docs['req_1'] ?? null,
                'review' => $getReviewInfo('req_1'),
                'note' => 'Upload PDF maksimal 20 MB',
            ],
            2 => [
                'key' => 'req_2',
                'title' => 'Akta Pendirian',
                'type' => 'wajib',
                'completed' => isset($docs['req_2']),
                'missing' => !isset($docs['req_2']),
                'file' => $docs['req_2'] ?? null,
                'review' => $getReviewInfo('req_2'),
                'note' => 'Akta Notaris memuat AD/ART (PDF 20 MB)',
            ],
            3 => [
                'key' => 'req_3',
                'title' => 'AHU dari Kemenkumham',
                'type' => 'wajib',
                'completed' => isset($docs['req_3']),
                'missing' => !isset($docs['req_3']),
                'file' => $docs['req_3'] ?? null,
                'review' => $getReviewInfo('req_3'),
                'note' => 'Upload PDF maksimal 20 MB',
            ],
            4 => [
                'key' => 'req_4',
                'title' => 'Program Kerja',
                'type' => 'wajib',
                'completed' => isset($docs['req_4']),
                'missing' => !isset($docs['req_4']),
                'file' => $docs['req_4'] ?? null,
                'review' => $getReviewInfo('req_4'),
                'note' => 'Upload PDF maksimal 20 MB',
            ],
            5 => [
                'key' => 'req_5',
                'title' => 'Susunan Pengurus dan SK',
                'type' => 'wajib',
                'completed' => isset($docs['req_5']),
                'missing' => !isset($docs['req_5']),
                'file' => $docs['req_5'] ?? null,
                'review' => $getReviewInfo('req_5'),
                'note' => 'Upload PDF maksimal 20 MB',
            ],
            6 => [
                'key' => 'req_6',
                'title' => 'Surat Keterangan Domisili Sekretariat',
                'type' => 'wajib',
                'completed' => isset($docs['req_6a']) && isset($docs['req_6b']),
                'missing' => !isset($docs['req_6a']) || !isset($docs['req_6b']),
                'sub_items' => [
                    '6a' => [
                        'key' => 'req_6a',
                        'title' => 'a. Keterangan Lurah',
                        'completed' => isset($docs['req_6a']),
                        'file' => $docs['req_6a'] ?? null,
                        'review' => $getReviewInfo('req_6a'),
                    ],
                    '6b' => [
                        'key' => 'req_6b',
                        'title' => 'b. Bukti Kepemilikan/Kontrak/Izin Pakai',
                        'completed' => isset($docs['req_6b']),
                        'file' => $docs['req_6b'] ?? null,
                        'review' => $getReviewInfo('req_6b'),
                    ],
                ],
                'review' => $getReviewInfo('req_6'),
                'note' => 'Keduanya (6a & 6b) wajib diupload',
            ],
            7 => [
                'key' => 'req_7',
                'title' => 'NPWP atas Nama Ormas',
                'type' => 'wajib',
                'completed' => isset($docs['req_7']),
                'missing' => !isset($docs['req_7']),
                'file' => $docs['req_7'] ?? null,
                'review' => $getReviewInfo('req_7'),
                'note' => 'Upload PDF maksimal 20 MB',
            ],
            8 => [
                'key' => 'req_8',
                'title' => 'Surat Pernyataan Tidak Dalam Sengketa Kepengurusan / Perkara',
                'type' => 'wajib',
                'completed' => isset($docs['req_8']),
                'missing' => !isset($docs['req_8']),
                'file' => $docs['req_8'] ?? null,
                'review' => $getReviewInfo('req_8'),
                'note' => 'Download template Word (kop surat) -> kumpulkan hasil scan PDF',
                'has_download' => true,
                'download_url' => asset('build/assets/form_kop_surat_ormas.docx'),
            ],
            9 => [
                'key' => 'req_9',
                'title' => 'Surat Pernyataan Formulir Isian Data Ormas',
                'type' => 'wajib',
                'completed' => $orgForm !== null && (bool)$orgForm->is_confirmed,
                'missing' => $orgForm === null || !(bool)$orgForm->is_confirmed,
                'form_data' => $orgForm,
                'review' => $getReviewInfo('req_9'),
                'note' => 'Diisi melalui form online di sistem',
                'is_online_form' => true,
            ],
            10 => [
                'key' => 'req_10',
                'title' => 'Surat Pernyataan Kesanggupan Melaporkan Kegiatan',
                'type' => 'wajib',
                'completed' => isset($docs['req_10']),
                'missing' => !isset($docs['req_10']),
                'file' => $docs['req_10'] ?? null,
                'review' => $getReviewInfo('req_10'),
                'note' => 'Upload PDF maksimal 20 MB',
            ],
            11 => [
                'key' => 'req_11',
                'title' => 'Rekom Kemenag bagi Ormas Keagamaan',
                'type' => 'kondisional',
                'is_religious' => (bool)$org->is_religious,
                'completed' => !$org->is_religious || isset($docs['req_11']),
                'missing' => $org->is_religious && !isset($docs['req_11']),
                'file' => $docs['req_11'] ?? null,
                'review' => $getReviewInfo('req_11'),
                'note' => $org->is_religious ? 'Wajib upload PDF karena Ormas Keagamaan' : 'Tidak wajib (bukan Ormas keagamaan)',
            ],
            12 => [
                'key' => 'req_12',
                'title' => 'Rekom Kementerian/Perangkat Daerah Bidang Kebudayaan',
                'type' => 'opsional',
                'completed' => true,
                'has_file' => isset($docs['req_12']),
                'file' => $docs['req_12'] ?? null,
                'review' => $getReviewInfo('req_12'),
                'note' => 'Opsional (untuk Ormas Kepercayaan)',
            ],
            13 => [
                'key' => 'req_13',
                'title' => 'Surat Pernyataan Kesediaan/Persetujuan Pejabat/Tokoh',
                'type' => 'opsional',
                'completed' => true,
                'has_file' => isset($docs['req_13']),
                'file' => $docs['req_13'] ?? null,
                'review' => $getReviewInfo('req_13'),
                'note' => 'Opsional',
            ],
            14 => [
                'key' => 'req_14',
                'title' => 'Biodata Pengurus (Ketua, Sekretaris, Bendahara)',
                'type' => 'wajib',
                'completed' => isset($docs['req_14a']) && isset($docs['req_14b']) && isset($docs['req_14c']),
                'missing' => !isset($docs['req_14a']) || !isset($docs['req_14b']) || !isset($docs['req_14c']),
                'sub_items' => [
                    '14a' => ['key' => 'req_14a', 'title' => 'Biodata Ketua', 'completed' => isset($docs['req_14a']), 'file' => $docs['req_14a'] ?? null, 'review' => $getReviewInfo('req_14a')],
                    '14b' => ['key' => 'req_14b', 'title' => 'Biodata Sekretaris', 'completed' => isset($docs['req_14b']), 'file' => $docs['req_14b'] ?? null, 'review' => $getReviewInfo('req_14b')],
                    '14c' => ['key' => 'req_14c', 'title' => 'Biodata Bendahara', 'completed' => isset($docs['req_14c']), 'file' => $docs['req_14c'] ?? null, 'review' => $getReviewInfo('req_14c')],
                ],
                'review' => $getReviewInfo('req_14'),
                'note' => 'Wajib 3 file PDF (Ketua, Sekretaris, Bendahara)',
            ],
            15 => [
                'key' => 'req_15',
                'title' => 'Pas Foto Warna Terbaru 4x6 (Ketua, Sekretaris, Bendahara)',
                'type' => 'wajib',
                'completed' => isset($docs['req_15a']) && isset($docs['req_15b']) && isset($docs['req_15c']),
                'missing' => !isset($docs['req_15a']) || !isset($docs['req_15b']) || !isset($docs['req_15c']),
                'sub_items' => [
                    '15a' => ['key' => 'req_15a', 'title' => 'Pas Foto Ketua', 'completed' => isset($docs['req_15a']), 'file' => $docs['req_15a'] ?? null, 'review' => $getReviewInfo('req_15a')],
                    '15b' => ['key' => 'req_15b', 'title' => 'Pas Foto Sekretaris', 'completed' => isset($docs['req_15b']), 'file' => $docs['req_15b'] ?? null, 'review' => $getReviewInfo('req_15b')],
                    '15c' => ['key' => 'req_15c', 'title' => 'Pas Foto Bendahara', 'completed' => isset($docs['req_15c']), 'file' => $docs['req_15c'] ?? null, 'review' => $getReviewInfo('req_15c')],
                ],
                'review' => $getReviewInfo('req_15'),
                'note' => 'Wajib 3 file PDF (Ketua, Sekretaris, Bendahara)',
            ],
            16 => [
                'key' => 'req_16',
                'title' => 'Fotocopy KTP-El + Data (Ketua, Sekretaris, Bendahara)',
                'type' => 'wajib',
                'completed' => isset($docs['req_16a']) && isset($docs['req_16b']) && isset($docs['req_16c']),
                'missing' => !isset($docs['req_16a']) || !isset($docs['req_16b']) || !isset($docs['req_16c']),
                'sub_items' => [
                    '16a' => ['key' => 'req_16a', 'title' => 'KTP Ketua + No HP/WA', 'completed' => isset($docs['req_16a']), 'file' => $docs['req_16a'] ?? null, 'review' => $getReviewInfo('req_16a')],
                    '16b' => ['key' => 'req_16b', 'title' => 'KTP Sekretaris + No HP/WA', 'completed' => isset($docs['req_16b']), 'file' => $docs['req_16b'] ?? null, 'review' => $getReviewInfo('req_16b')],
                    '16c' => ['key' => 'req_16c', 'title' => 'KTP Bendahara + No HP/WA', 'completed' => isset($docs['req_16c']), 'file' => $docs['req_16c'] ?? null, 'review' => $getReviewInfo('req_16c')],
                ],
                'review' => $getReviewInfo('req_16'),
                'note' => 'Wajib file PDF + No HP & WhatsApp aktif',
            ],
        ];

        // Total 16 requirement slots
        $totalItems = 16;
        $completedCount = 0;
        $missingTitles = [];

        // Count review statuses
        $reviewSummary = [
            'approved' => 0,
            'revision_needed' => 0,
            'rejected' => 0,
            'pending' => 0,
            'not_applicable' => 0,
        ];

        foreach ($requirements as $key => $req) {
            if ($req['completed']) {
                $completedCount++;
            } else {
                $missingTitles[] = "Persyaratan No. {$key}: {$req['title']}";
            }

            $st = $req['review']['status'];
            if (isset($reviewSummary[$st])) {
                $reviewSummary[$st]++;
            } else {
                $reviewSummary['pending']++;
            }
        }

        $percentage = min(100, round(($completedCount / $totalItems) * 100));
        $isFullyComplete = ($completedCount === $totalItems);

        return [
            'requirements' => $requirements,
            'completed_count' => $completedCount,
            'total_items' => $totalItems,
            'percentage' => $percentage,
            'is_fully_complete' => $isFullyComplete,
            'missing_titles' => $missingTitles,
            'review_summary' => $reviewSummary,
            'organization' => $org,
            'organization_form' => $orgForm,
        ];
    }
}
