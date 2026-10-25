<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\ChatMessage;
use App\Models\Organization;
use App\Models\User;
use App\Services\HistoryCleaner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistoryCleanerTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_chats_and_notifications_are_cleaned_up_after_30_days(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'user', 'login_id' => '100001']);
        $admin = User::factory()->create(['role' => 'admin', 'login_id' => '900001']);

        $organization = Organization::create([
            'user_id' => $user->id,
            'name' => 'Test Ormas',
            'status' => 'active',
        ]);

        // Create recent chat and notification
        $recentChat = ChatMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $admin->id,
            'organization_id' => $organization->id,
            'message' => 'Pesan Baru',
            'created_at' => now()->subDays(5),
        ]);

        $recentNotification = AdminNotification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Notifikasi Baru',
            'message' => 'Detail baru',
            'created_at' => now()->subDays(5),
        ]);

        // Create old chat (35 days old) with attachment
        $attachmentPath = 'chat_attachments/' . $organization->id . '/old_file.pdf';
        Storage::disk('local')->put($attachmentPath, 'dummy content');

        $oldChat = ChatMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $admin->id,
            'organization_id' => $organization->id,
            'message' => 'Pesan Lama',
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => 'old_file.pdf',
        ]);
        ChatMessage::where('id', $oldChat->id)->update(['created_at' => now()->subDays(35)]);

        // Create old notification (35 days old)
        $oldNotification = AdminNotification::create([
            'user_id' => $user->id,
            'type' => 'test',
            'title' => 'Notifikasi Lama',
            'message' => 'Detail lama',
        ]);
        AdminNotification::where('id', $oldNotification->id)->update(['created_at' => now()->subDays(35)]);


        // Execute history cleanup
        HistoryCleaner::cleanOldHistory();

        // Assert recent data remains
        $this->assertDatabaseHas('chat_messages', ['id' => $recentChat->id]);
        $this->assertDatabaseHas('admin_notifications', ['id' => $recentNotification->id]);

        // Assert old data and attachment file are deleted
        $this->assertDatabaseMissing('chat_messages', ['id' => $oldChat->id]);
        $this->assertDatabaseMissing('admin_notifications', ['id' => $oldNotification->id]);
        Storage::disk('local')->assertMissing($attachmentPath);
    }

    public function test_artisan_history_clean_command_runs_successfully(): void
    {
        $this->artisan('history:clean')
             ->assertExitCode(0);
    }
}
