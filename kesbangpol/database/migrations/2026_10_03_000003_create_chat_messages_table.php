<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
                $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
                $table->text('message');
                $table->string('attachment_path')->nullable();
                $table->string('attachment_original_name')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['organization_id', 'created_at']);
                $table->index(['sender_id', 'receiver_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
