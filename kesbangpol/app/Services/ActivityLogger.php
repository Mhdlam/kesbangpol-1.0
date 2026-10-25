<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, string $description, ?string $subjectType = null, ?int $subjectId = null, ?User $user = null): ?ActivityLog
    {
        $actor = $user ?? Auth::user();

        // Only log activities performed by Admin users
        if (!$actor || !$actor->isAdmin()) {
            return null;
        }

        return ActivityLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'ip_address' => request()->ip(),
        ]);
    }

    public static function notifyAdmins(string $type, string $title, string $message, ?string $actionUrl = null, ?int $targetAdminId = null): void
    {
        if ($targetAdminId) {
            AdminNotification::create([
                'user_id' => $targetAdminId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
            ]);
        } else {
            // Notify all admins
            $adminIds = User::where('role', 'admin')->pluck('id');
            foreach ($adminIds as $adminId) {
                AdminNotification::create([
                    'user_id' => $adminId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $actionUrl,
                ]);
            }
        }
    }

    public static function notifyUser(int|User $targetUser, string $type, string $title, string $message, ?string $actionUrl = null): void
    {
        $userId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        AdminNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ]);
    }

    public static function notifyAllUsers(string $type, string $title, string $message, ?string $actionUrl = null): void
    {
        $userIds = User::where('role', 'user')->pluck('id');
        foreach ($userIds as $userId) {
            AdminNotification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
            ]);
        }
    }
}
