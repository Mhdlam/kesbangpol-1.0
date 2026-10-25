<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\HistoryCleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        HistoryCleaner::cleanOldHistory();

        $notifications = AdminNotification::where('user_id', Auth::id())

            ->latest()
            ->paginate(15);

        return view('user.notifications', compact('notifications'));
    }

    public function markAsRead(AdminNotification $notification)
    {
        if ($notification->user_id === Auth::id()) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        AdminNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function fetchUnread()
    {
        $count = AdminNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        $latest = AdminNotification::where('user_id', Auth::id())
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'message' => $item->message,
                    'is_read' => $item->is_read,
                    'action_url' => $item->action_url ?: route('user.notifications'),
                    'time_ago' => $item->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'unread_count' => $count,
            'notifications' => $latest,
        ]);
    }
}
