<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Organization;
use App\Models\Submission;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\HistoryCleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = Organization::with(['user', 'submissions' => function ($q) {
            $q->latest();
        }])
        ->withCount(['chatMessages as unread_count' => function ($q) {
            $q->where('is_read', false)->where('sender_id', '!=', Auth::id());
        }])
        ->withMax('chatMessages as latest_message_at', 'created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('abbreviation', 'like', "%{$search}%")
                  ->orWhere('leader_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('login_id', 'like', "%{$search}%");
                  });
            });
        }

        // Get organizations sorted by latest chat message timestamp directly in MySQL
        $organizations = $query->orderByRaw('COALESCE(latest_message_at, created_at) DESC')->get();

        // Default selected organization if available
        $activeOrganization = null;
        if ($request->has('org')) {
            $activeOrganization = Organization::find($request->input('org'));
        } elseif ($organizations->isNotEmpty()) {
            $activeOrganization = $organizations->first();
        }

        $messages = collect();
        $latestSubmission = null;

        if ($activeOrganization) {
            // Mark unread messages for this active org as read
            ChatMessage::where('organization_id', $activeOrganization->id)
                ->where('sender_id', '!=', Auth::id())
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);

            $messages = ChatMessage::where('organization_id', $activeOrganization->id)
                ->with(['sender', 'submission'])
                ->orderBy('created_at', 'asc')
                ->get();

            $latestSubmission = Submission::where('organization_id', $activeOrganization->id)->latest()->first();
        }

        return view('admin.chat.index', compact('organizations', 'activeOrganization', 'messages', 'latestSubmission', 'search'));
    }

    public function show(Organization $organization)
    {
        return redirect()->route('admin.chat.index', ['org' => $organization->id]);
    }

    public function store(Request $request, Organization $organization)
    {
        $request->validate([
            'message' => ['required_without:attachment', 'nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'message.required_without' => 'Pesan atau dokumen lampiran tidak boleh kosong.',
            'attachment.mimes' => 'Lampiran harus berupa PDF atau Gambar (JPG/PNG).',
            'attachment.max' => 'Ukuran file lampiran maksimal 10MB.',
        ]);

        $admin = Auth::user();
        $attachmentPath = null;
        $attachmentOriginalName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentOriginalName = $file->getClientOriginalName();
            $fileName = 'chat_admin_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('chat_attachments/' . $organization->id, $fileName, 'local');
        }

        $latestSubmission = Submission::where('organization_id', $organization->id)->latest()->first();

        $chat = ChatMessage::create([
            'sender_id' => $admin->id,
            'receiver_id' => $organization->user_id,
            'organization_id' => $organization->id,
            'submission_id' => $latestSubmission?->id,
            'message' => $request->input('message') ?: '[Mengirim Lampiran]',
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'is_read' => false,
        ]);

        // Send system notification to Ormas User
        ActivityLogger::notifyUser(
            $organization->user_id,
            'chat_message',
            'Pesan Chat Baru dari Admin Kesbangpol',
            $request->input('message') ? Str::limit($request->input('message'), 80) : 'Admin mengirimkan berkas lampiran.',
            route('user.chat')
        );

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $chat->id,
                    'sender_name' => 'Admin Bakesbangpol (' . $admin->name . ')',
                    'sender_role' => 'Admin Bakesbangpol',
                    'is_me' => true,
                    'organization_name' => $organization->name,
                    'message' => $chat->message,
                    'attachment_url' => $chat->attachment_path ? route('chat.download_attachment', $chat->id) : null,
                    'attachment_name' => $chat->attachment_original_name,
                    'created_at_formatted' => $chat->created_at->translatedFormat('d M Y, H:i') . ' WIB',
                    'is_read' => false,
                ]
            ]);
        }

        return redirect()->route('admin.chat.index', ['org' => $organization->id])->with('success', 'Pesan berhasil dikirim ke ' . $organization->name);
    }

    public function fetchMessages(Organization $organization)
    {
        $admin = Auth::user();


        // Mark incoming unread messages as read
        ChatMessage::where('organization_id', $organization->id)
            ->where('sender_id', '!=', $admin->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = ChatMessage::where('organization_id', $organization->id)
            ->with(['sender'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($admin, $organization) {
                $isMe = ($msg->sender_id === $admin->id);
                return [
                    'id' => $msg->id,
                    'sender_name' => $isMe ? 'Admin (' . $admin->name . ')' : ($msg->sender ? $msg->sender->name : 'Pengurus Ormas'),
                    'sender_role' => $isMe ? 'Admin Bakesbangpol' : 'Pengurus Ormas (' . $organization->name . ')',
                    'is_me' => $isMe,
                    'organization_name' => $organization->name,
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_path ? route('chat.download_attachment', $msg->id) : null,
                    'attachment_name' => $msg->attachment_original_name,
                    'created_at_formatted' => $msg->created_at->translatedFormat('d M Y, H:i') . ' WIB',
                    'is_read' => $msg->is_read,
                ];
            });

        return response()->json([
            'messages' => $messages,
            'organization_name' => $organization->name,
        ]);
    }

    public function fetchUnreadCount()
    {
        $count = ChatMessage::where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function sendBroadcastNotification(Request $request)
    {
        $request->validate([
            'target_type' => ['required', 'in:all,specific,multiple'],
            'target_user_id' => ['nullable', 'required_if:target_type,specific', 'exists:users,id'],
            'target_user_ids' => ['nullable', 'required_if:target_type,multiple', 'array'],
            'target_user_ids.*' => ['exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'action_url' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'Judul notifikasi pengumuman wajib diisi.',
            'message.required' => 'Isi pesan pengumuman wajib diisi.',
            'target_user_ids.required_if' => 'Silakan pilih setidaknya satu Ormas penerima.',
        ]);

        $targetType = $request->input('target_type');

        if ($targetType === 'all') {
            ActivityLogger::notifyAllUsers(
                'broadcast_announcement',
                $request->input('title'),
                $request->input('message'),
                $request->input('action_url') ?: route('user.dashboard')
            );
            ActivityLogger::log('broadcast_notification', "Mengirimkan pengumuman publik ke seluruh Ormas: {$request->input('title')}");
            return redirect()->back()->with('success', 'Pengumuman notifikasi berhasil dikirimkan ke SELURUH Ormas.');
        } elseif ($targetType === 'multiple') {
            $userIds = array_filter((array) $request->input('target_user_ids'));
            foreach ($userIds as $userId) {
                ActivityLogger::notifyUser(
                    (int) $userId,
                    'direct_announcement',
                    $request->input('title'),
                    $request->input('message'),
                    $request->input('action_url') ?: route('user.dashboard')
                );
            }
            $count = count($userIds);
            ActivityLogger::log('broadcast_notification', "Mengirimkan pengumuman ke {$count} Ormas terpilih: {$request->input('title')}");
            return redirect()->back()->with('success', "Notifikasi pengumuman berhasil dikirimkan khusus kepada {$count} Ormas terpilih.");
        } else {
            $user = User::findOrFail($request->input('target_user_id'));
            ActivityLogger::notifyUser(
                $user->id,
                'direct_announcement',
                $request->input('title'),
                $request->input('message'),
                $request->input('action_url') ?: route('user.dashboard')
            );
            ActivityLogger::log('direct_notification', "Mengirimkan notifikasi langsung ke Ormas {$user->name}: {$request->input('title')}");
            return redirect()->back()->with('success', "Notifikasi pengumuman berhasil dikirimkan khusus kepada Ormas {$user->name}.");
        }
    }
}

