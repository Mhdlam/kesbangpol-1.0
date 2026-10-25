<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HistoryCleaner
{
    /**
     * Clean up chat messages and notifications older than 30 days.
     */
    public static function cleanOldHistory(): void
    {
        try {
            $cutoffDate = now()->subDays(30);

            // Clean old chat messages & their attachments
            $oldChatMessages = ChatMessage::where('created_at', '<=', $cutoffDate)->get();
            foreach ($oldChatMessages as $message) {
                if ($message->attachment_path && Storage::disk('local')->exists($message->attachment_path)) {
                    Storage::disk('local')->delete($message->attachment_path);
                }
                $message->delete();
            }

            // Clean old notifications
            AdminNotification::where('created_at', '<=', $cutoffDate)->delete();

            // Clean old activity logs (older than 30 days)
            \App\Models\ActivityLog::where('created_at', '<=', $cutoffDate)->delete();
        } catch (\Throwable $e) {
            Log::error('HistoryCleaner Error: ' . $e->getMessage());
        }
    }
}
