<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->string('nama_ormas');
            $table->string('bidang_kegiatan')->nullable();
            $table->text('alamat_sekretariat')->nullable();
            $table->string('nomor_akta_pendirian')->nullable();
            $table->string('nama_notaris')->nullable();
            $table->string('nomor_sk_kemenkumham')->nullable();
            $table->string('nama_ketua')->nullable();
            $table->string('nama_sekretaris')->nullable();
            $table->string('nama_bendahara')->nullable();
            $table->json('extra_fields')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_forms');
    }
};
