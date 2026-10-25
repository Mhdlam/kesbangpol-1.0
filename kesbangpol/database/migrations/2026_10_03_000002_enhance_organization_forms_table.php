<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('organization_forms', 'tempat_waktu_pendirian')) {
                $table->string('tempat_waktu_pendirian')->nullable()->after('nomor_sk_kemenkumham');
            }
            if (!Schema::hasColumn('organization_forms', 'asas_ciri_organisasi')) {
                $table->string('asas_ciri_organisasi')->nullable()->after('tempat_waktu_pendirian');
            }
            if (!Schema::hasColumn('organization_forms', 'tujuan_organisasi')) {
                $table->text('tujuan_organisasi')->nullable()->after('asas_ciri_organisasi');
            }
            if (!Schema::hasColumn('organization_forms', 'nama_pendiri')) {
                $table->text('nama_pendiri')->nullable()->after('tujuan_organisasi');
            }
            if (!Schema::hasColumn('organization_forms', 'nama_pembina')) {
                $table->text('nama_pembina')->nullable()->after('nama_pendiri');
            }
            if (!Schema::hasColumn('organization_forms', 'nama_penasehat')) {
                $table->text('nama_penasehat')->nullable()->after('nama_pembina');
            }
            if (!Schema::hasColumn('organization_forms', 'masa_bhakti')) {
                $table->string('masa_bhakti')->nullable()->after('nama_bendahara');
            }
            if (!Schema::hasColumn('organization_forms', 'keputusan_tertinggi')) {
                $table->string('keputusan_tertinggi')->nullable()->after('masa_bhakti');
            }
            if (!Schema::hasColumn('organization_forms', 'unit_sayap_otonom')) {
                $table->text('unit_sayap_otonom')->nullable()->after('keputusan_tertinggi');
            }
            if (!Schema::hasColumn('organization_forms', 'usaha_organisasi')) {
                $table->text('usaha_organisasi')->nullable()->after('unit_sayap_otonom');
            }
            if (!Schema::hasColumn('organization_forms', 'sumber_keuangan')) {
                $table->string('sumber_keuangan')->nullable()->after('usaha_organisasi');
            }
            if (!Schema::hasColumn('organization_forms', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('sumber_keuangan');
            }
            if (!Schema::hasColumn('organization_forms', 'bendera_path')) {
                $table->string('bendera_path')->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('organization_forms', 'signature_path')) {
                $table->string('signature_path')->nullable()->after('bendera_path');
            }
            if (!Schema::hasColumn('organization_forms', 'cap_path')) {
                $table->string('cap_path')->nullable()->after('signature_path');
            }
            if (!Schema::hasColumn('organization_forms', 'excel_path')) {
                $table->string('excel_path')->nullable()->after('cap_path');
            }
            if (!Schema::hasColumn('organization_forms', 'is_confirmed')) {
                $table->boolean('is_confirmed')->default(false)->after('excel_path');
            }
            if (!Schema::hasColumn('organization_forms', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('is_confirmed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organization_forms', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_waktu_pendirian', 'asas_ciri_organisasi', 'tujuan_organisasi',
                'nama_pendiri', 'nama_pembina', 'nama_penasehat', 'masa_bhakti',
                'keputusan_tertinggi', 'unit_sayap_otonom', 'usaha_organisasi',
                'sumber_keuangan', 'logo_path', 'bendera_path', 'signature_path',
                'cap_path', 'excel_path', 'is_confirmed', 'confirmed_at'
            ]);
        });
    }
};
