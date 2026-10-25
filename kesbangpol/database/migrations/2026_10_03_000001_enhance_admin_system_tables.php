<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Enhance Submissions table
        Schema::table('submissions', function (Blueprint $table) {
            if (!Schema::hasColumn('submissions', 'assigned_admin_id')) {
                $table->foreignId('assigned_admin_id')->nullable()->after('status_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('submissions', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_admin_id');
            }
            if (!Schema::hasColumn('submissions', 'review_started_at')) {
                $table->timestamp('review_started_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('submissions', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('review_started_at');
            }
            if (!Schema::hasColumn('submissions', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('reviewed_at');
            }
            if (!Schema::hasColumn('submissions', 'revision_count')) {
                $table->integer('revision_count')->default(0)->after('completed_at');
            }
            if (!Schema::hasColumn('submissions', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('revision_count');
            }
            if (!Schema::hasColumn('submissions', 'district')) {
                $table->string('district')->nullable()->after('title');
            }
        });

        // 2. Enhance Organizations table
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'status')) {
                $table->enum('status', ['active', 'inactive', 'archived'])->default('active')->after('is_religious');
            }
            if (!Schema::hasColumn('organizations', 'abbreviation')) {
                $table->string('abbreviation')->nullable()->after('name');
            }
            if (!Schema::hasColumn('organizations', 'registration_number')) {
                $table->string('registration_number')->nullable()->after('abbreviation');
            }
            if (!Schema::hasColumn('organizations', 'district')) {
                $table->string('district')->nullable()->after('address');
            }
            if (!Schema::hasColumn('organizations', 'category')) {
                $table->string('category')->nullable()->after('district');
            }
            if (!Schema::hasColumn('organizations', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('status');
            }
        });

        // 3. Enhance Users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (!Schema::hasColumn('users', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('is_active');
            }
        });

        // 4. Create submission_document_reviews table
        if (!Schema::hasTable('submission_document_reviews')) {
            Schema::create('submission_document_reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
                $table->string('document_key');
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
                $table->enum('status', ['pending', 'approved', 'rejected', 'revision_needed', 'not_applicable'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create submission_document_versions table
        if (!Schema::hasTable('submission_document_versions')) {
            Schema::create('submission_document_versions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
                $table->string('document_key');
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->integer('version')->default(1);
                $table->string('original_name');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('file_mime')->nullable();
                $table->timestamps();
            });
        }

        // 6. Create admin_internal_notes table
        if (!Schema::hasTable('admin_internal_notes')) {
            Schema::create('admin_internal_notes', function (Blueprint $table) {
                $table->id();
                $table->enum('target_type', ['organization', 'submission']);
                $table->unsignedBigInteger('target_id');
                $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
                $table->text('note');
                $table->timestamps();

                $table->index(['target_type', 'target_id']);
            });
        }

        // 7. Create admin_notifications table
        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // specific admin or null for all admins
                $table->string('type');
                $table->string('title');
                $table->text('message');
                $table->string('action_url')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        // 8. Create activity_logs table
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action');
                $table->text('description');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('admin_internal_notes');
        Schema::dropIfExists('submission_document_versions');
        Schema::dropIfExists('submission_document_reviews');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'last_activity_at']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['status', 'abbreviation', 'registration_number', 'district', 'category', 'archived_at']);
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropForeign(['assigned_admin_id']);
            $table->dropColumn([
                'assigned_admin_id', 'assigned_at', 'review_started_at', 
                'reviewed_at', 'completed_at', 'revision_count', 
                'is_locked', 'district'
            ]);
        });
    }
};
