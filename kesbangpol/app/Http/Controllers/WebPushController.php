<?php

namespace App\Http\Controllers;

use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebPushController extends Controller
{
    /**
     * Return VAPID Public Key for client-side registration.
     */
    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => config('webpush.vapid.public_key') ?: env('VAPID_PUBLIC_KEY', ''),
        ]);
    }

    /**
     * Store or update Web Push Subscription for the authenticated user.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:1024'],
            'keys.p256dh' => ['nullable', 'string'],
            'keys.auth' => ['nullable', 'string'],
            'public_key' => ['nullable', 'string'],
            'auth_token' => ['nullable', 'string'],
            'content_encoding' => ['nullable', 'string', 'in:aes128gcm,aesgcm'],
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $endpoint = $request->input('endpoint');
        $key = $request->input('keys.p256dh') ?? $request->input('public_key');
        $token = $request->input('keys.auth') ?? $request->input('auth_token');
        $contentEncoding = $request->input('content_encoding', 'aes128gcm');

        $subscription = $user->updatePushSubscription($endpoint, $key, $token, $contentEncoding);

        return response()->json([
            'success' => true,
            'message' => 'Perangkat Anda berhasil terdaftar untuk menerima notifikasi desktop.',
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Remove Web Push Subscription when user turns off desktop notifications.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $user = Auth::user();
        if ($user) {
            $user->deletePushSubscription($request->input('endpoint'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi desktop untuk perangkat ini telah dinonaktifkan.',
        ]);
    }

    /**
     * Send a test Web Push notification to the authenticated user.
     */
    public function test(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        if ($user->pushSubscriptions()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada perangkat yang terdaftar untuk menerima notifikasi desktop akun Anda.',
            ], 422);
        }

        $targetUrl = $user->isAdmin() ? route('admin.notifications') : route('user.notifications');

        $sent = WebPushService::sendToUser(
            $user,
            'Uji Coba Notifikasi Desktop',
            'Selamat! Notifikasi Web Push Kesbangpol berhasil terhubung dengan perangkat Windows 11 Anda.',
            $targetUrl,
            'test_notification'
        );

        if ($sent) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi uji coba berhasil dikirim. Periksa pop-up notifikasi di layar komputer Anda.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirimkan notifikasi uji coba. Pastikan izin notifikasi diizinkan pada browser.',
        ], 500);
    }

    /**
     * Get user's push notification subscription status.
     */
    public function status(): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['has_subscription' => false, 'count' => 0]);
        }

        $count = $user->pushSubscriptions()->count();

        return response()->json([
            'has_subscription' => $count > 0,
            'count' => $count,
        ]);
    }
}
