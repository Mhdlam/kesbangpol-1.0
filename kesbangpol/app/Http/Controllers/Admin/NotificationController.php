<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\HistoryCleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        HistoryCleaner::cleanOldHistory();

        $adminId = Auth::id();


        $query = AdminNotification::where(function ($q) use ($adminId) {
            $q->whereNull('user_id')->orWhere('user_id', $adminId);
        });

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $unreadCount = AdminNotification::where(function ($q) use ($adminId) {
            $q->whereNull('user_id')->orWhere('user_id', $adminId);
        })->where('is_read', false)->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return redirect()->back();
    }

    public function markAllAsRead()
    {
        $adminId = Auth::id();

        AdminNotification::where(function ($q) use ($adminId) {
            $q->whereNull('user_id')->orWhere('user_id', $adminId);
        })->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Semua notifikasi ditandai telah dibaca.');
    }
}
