<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WebPushGenericNotification;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    /**
     * Send Web Push notification to a specific user.
     */
    public static function sendToUser(int|User $targetUser, string $title, string $message, ?string $actionUrl = null, ?string $type = null): bool
    {
        try {
            $user = $targetUser instanceof User ? $targetUser : User::find($targetUser);
            if (!$user) {
                return false;
            }

            // If user has no active web push subscriptions, skip silently
            if ($user->pushSubscriptions()->count() === 0) {
                return false;
            }

            // Sanitize title & body: limit length to prevent overwhelming OS popup
            $cleanTitle = mb_substr(strip_tags($title), 0, 100);
            $cleanBody = mb_substr(strip_tags($message), 0, 250);

            $user->notify(new WebPushGenericNotification($cleanTitle, $cleanBody, $actionUrl, $type));

            return true;
        } catch (\Throwable $e) {
            Log::warning('WebPushService::sendToUser failed: ' . $e->getMessage(), [
                'user_id' => $targetUser instanceof User ? $targetUser->id : $targetUser,
                'title' => $title,
            ]);
            return false;
        }
    }

    /**
     * Send Web Push notification to multiple users.
     */
    public static function sendToUsers(iterable $userIds, string $title, string $message, ?string $actionUrl = null, ?string $type = null): int
    {
        $sentCount = 0;
        foreach ($userIds as $userId) {
            if (self::sendToUser($userId, $title, $message, $actionUrl, $type)) {
                $sentCount++;
            }
        }
        return $sentCount;
    }
}
