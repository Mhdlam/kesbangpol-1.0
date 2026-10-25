<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('document_key'); // req_1, req_2, req_6a, req_16a, etc.
            $table->string('original_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size');
            $table->string('file_mime')->default('application/pdf');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'document_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_documents');
    }
};
