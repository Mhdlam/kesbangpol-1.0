<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->index(['status_id', 'created_at'], 'idx_sub_status_created');
            $table->index(['assigned_admin_id'], 'idx_sub_assigned_admin');
            $table->index(['completed_at'], 'idx_sub_completed');
            $table->index(['user_id'], 'idx_sub_user');
            $table->index(['district'], 'idx_sub_district');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->index(['status'], 'idx_org_status');
            $table->index(['district'], 'idx_org_district');
            $table->index(['name'], 'idx_org_name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_active'], 'idx_users_role_active');
        });

        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_admin_notif_user_read');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_act_user_created');
            $table->index(['created_at'], 'idx_act_created');
        });

        Schema::table('submission_document_reviews', function (Blueprint $table) {
            $table->index(['submission_id', 'status'], 'idx_doc_rev_sub_status');
        });
    }

    public function down(): void
    {
        Schema::table('submission_document_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_doc_rev_sub_status');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_act_user_created');
            $table->dropIndex('idx_act_created');
        });

        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->dropIndex('idx_admin_notif_user_read');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_role_active');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('idx_org_status');
            $table->dropIndex('idx_org_district');
            $table->dropIndex('idx_org_name');
        });

        Schema::table('submissions', function (Blueprint $table) {
            $table->dropIndex('idx_sub_status_created');
            $table->dropIndex('idx_sub_assigned_admin');
            $table->dropIndex('idx_sub_completed');
            $table->dropIndex('idx_sub_user');
            $table->dropIndex('idx_sub_district');
        });
    }
};
