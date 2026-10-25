<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StatusSeeder::class,
            DemoDataSeeder::class,
        ]);

        // Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@kesbangpol.palembang.go.id'],
            [
                'name' => 'Administrator Kesbangpol',
                'login_id' => '123456',
                'role' => 'admin',
                'password' => Hash::make('password123'),
                'plain_password' => 'password123',
                'phone_wa' => '081234567890',
                'position' => 'Kepala Bidang Ormas',
                'bio' => 'Petugas Kesbangpol Kota Palembang',
            ]
        );

        // Ormas User Account
        $user = User::updateOrCreate(
            ['email' => 'ormas@mahasiswa.ac.id'],
            [
                'name' => 'Ahmad Hidayat (Ketua HMI)',
                'login_id' => '654321',
                'role' => 'user',
                'password' => Hash::make('password123'),
                'plain_password' => 'password123',
                'phone_wa' => '089876543210',
                'university' => 'Universitas Sriwijaya',
                'bio' => 'Ketua Umum Organisasi Mahasiswa',
            ]
        );

        Organization::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Himpunan Mahasiswa Islam (HMI) Palembang',
                'university' => 'Universitas Sriwijaya',
                'address' => 'Jl. Jalan Kapten A. Rivai No. 12, Palembang',
                'leader_name' => 'Ahmad Hidayat',
                'secretary_name' => 'Budi Pratama',
                'treasurer_name' => 'Citra Lestari',
                'is_religious' => true,
            ]
        );
    }
}
