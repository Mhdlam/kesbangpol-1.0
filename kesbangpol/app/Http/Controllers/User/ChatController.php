<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Submission;
use App\Services\ActivityLogger;
use App\Services\HistoryCleaner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index()
    {
        HistoryCleaner::cleanOldHistory();

        $user = Auth::user();
        $organization = $user->organization;

        $latestSubmission = Submission::where('user_id', $user->id)
            ->latest()
            ->first();

        // Mark incoming unread messages as read
        if ($organization) {
            ChatMessage::where('organization_id', $organization->id)
                ->where('sender_id', '!=', $user->id)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        $messages = $organization
            ? ChatMessage::where('organization_id', $organization->id)
                ->with(['sender', 'submission'])
                ->orderBy('created_at', 'asc')
                ->get()
            : collect();

        $isAnyAdminOnline = \App\Models\User::where('role', 'admin')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->exists();

        return view('user.chat', compact('user', 'organization', 'latestSubmission', 'messages', 'isAnyAdminOnline'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => ['required_without:attachment', 'nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB max
        ], [
            'message.required_without' => 'Pesan atau lampiran tidak boleh kosong.',
            'attachment.mimes' => 'Lampiran harus berupa file PDF atau Gambar (JPG/PNG).',
            'attachment.max' => 'Ukuran lampiran maksimal 10MB.',
        ]);

        $user = Auth::user();
        $organization = $user->organization;

        if (!$organization) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Silakan lengkapi profil organisasi Ormas Anda terlebih dahulu.'], 422);
            }
            return redirect()->back()->with('error', 'Silakan lengkapi profil organisasi Ormas Anda terlebih dahulu sebelum menggunakan layanan chat.');
        }

        $attachmentPath = null;
        $attachmentOriginalName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentOriginalName = $file->getClientOriginalName();
            $fileName = 'chat_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('chat_attachments/' . $organization->id, $fileName, 'local');
        }

        $latestSubmission = Submission::where('user_id', $user->id)->latest()->first();

        $chat = ChatMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => null, // Sent to admin team
            'organization_id' => $organization->id,
            'submission_id' => $latestSubmission?->id,
            'message' => $request->input('message') ?: '[Mengirim Lampiran]',
            'attachment_path' => $attachmentPath,
            'attachment_original_name' => $attachmentOriginalName,
            'is_read' => false,
        ]);

        // Notify Admins about new message from Ormas
        ActivityLogger::notifyAdmins(
            'chat_message',
            'Pesan Chat Baru dari Ormas ' . ($organization->name ?: $user->name),
            $request->input('message') ? Str::limit($request->input('message'), 80) : 'Mengirimkan dokumen lampiran',
            route('admin.chat.show', $organization->id)
        );

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $chat->id,
                    'sender_name' => $user->name,
                    'sender_role' => 'Pengurus Ormas',
                    'is_me' => true,
                    'message' => $chat->message,
                    'attachment_url' => $chat->attachment_path ? route('chat.download_attachment', $chat->id) : null,
                    'attachment_name' => $chat->attachment_original_name,
                    'created_at_formatted' => $chat->created_at->translatedFormat('d M Y, H:i') . ' WIB',
                    'is_read' => false,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Pesan terkirim.');
    }

    public function fetchMessages()
    {
        HistoryCleaner::cleanOldHistory();

        $user = Auth::user();

        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['messages' => [], 'unread_count' => 0]);
        }

        // Mark incoming messages as read when polling
        ChatMessage::where('organization_id', $organization->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $messages = ChatMessage::where('organization_id', $organization->id)
            ->with(['sender'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user, $organization) {
                $isMe = ($msg->sender_id === $user->id);
                return [
                    'id' => $msg->id,
                    'sender_name' => $msg->sender ? $msg->sender->name : 'Sistem',
                    'sender_role' => $isMe ? 'Pengurus Ormas' : ($msg->sender?->isAdmin() ? 'Admin Bakesbangpol' : 'Pengurus Ormas'),
                    'is_me' => $isMe,
                    'organization_name' => $organization->name ?: $user->name,
                    'message' => $msg->message,
                    'attachment_url' => $msg->attachment_path ? route('chat.download_attachment', $msg->id) : null,
                    'attachment_name' => $msg->attachment_original_name,
                    'created_at_formatted' => $msg->created_at->translatedFormat('d M Y, H:i') . ' WIB',
                    'is_read' => $msg->is_read,
                ];
            });

        $isAnyAdminOnline = \App\Models\User::where('role', 'admin')
            ->where('last_activity_at', '>=', now()->subMinutes(5))
            ->exists();

        return response()->json([
            'messages' => $messages,
            'organization_name' => $organization->name ?: $user->name,
            'is_admin_online' => $isAnyAdminOnline,
        ]);
    }

    public function fetchUnreadCount()
    {
        $user = Auth::user();
        $organization = $user->organization;

        if (!$organization) {
            return response()->json(['unread_count' => 0]);
        }

        $count = ChatMessage::where('organization_id', $organization->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    public function downloadAttachment(ChatMessage $message)
    {
        $user = Auth::user();

        // Authorization check: User can download if they belong to org or are admin
        if (!$user->isAdmin() && $message->organization_id !== $user->organization?->id) {
            abort(403, 'Anda tidak memiliki akses ke berkas lampiran ini.');
        }

        if (!$message->attachment_path || !Storage::disk('local')->exists($message->attachment_path)) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        return Storage::disk('local')->download($message->attachment_path, $message->attachment_original_name ?: 'lampiran_chat');
    }
}
