<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Belum Diperiksa',
                'code' => Status::SUBMITTED,
                'badge_color' => 'sky',
            ],
            [
                'name' => 'Sedang Diperiksa',
                'code' => Status::IN_PROCESS,
                'badge_color' => 'amber',
            ],
            [
                'name' => 'Perlu Revisi',
                'code' => Status::REVISION_NEEDED,
                'badge_color' => 'orange',
            ],
            [
                'name' => 'Berkas Diterima',
                'code' => Status::APPROVED,
                'badge_color' => 'emerald',
            ],
            [
                'name' => 'Berkas Ditolak',
                'code' => Status::REJECTED,
                'badge_color' => 'rose',
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(['code' => $status['code']], $status);
        }
    }
}
