<?php

namespace Tests\Feature;

use App\Models\Status;
use App\Models\User;
use App\Notifications\WebPushGenericNotification;
use App\Services\ActivityLogger;
use App\Services\WebPushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\PushSubscription;
use Tests\TestCase;

class WebPushNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Status::firstOrCreate(['code' => Status::SUBMITTED], ['name' => 'Diajukan', 'badge_color' => 'sky']);
        Status::firstOrCreate(['code' => Status::IN_PROCESS], ['name' => 'Sedang Diproses', 'badge_color' => 'blue']);
        Status::firstOrCreate(['code' => Status::REVISION_NEEDED], ['name' => 'Perlu Direvisi', 'badge_color' => 'amber']);
        Status::firstOrCreate(['code' => Status::APPROVED], ['name' => 'Disetujui', 'badge_color' => 'emerald']);
        Status::firstOrCreate(['code' => Status::REJECTED], ['name' => 'Ditolak', 'badge_color' => 'rose']);
    }

    public function test_unauthenticated_user_cannot_access_webpush_endpoints(): void
    {
        $this->getJson(route('webpush.vapid_public_key'))->assertStatus(401);
        $this->postJson(route('webpush.subscribe'), [])->assertStatus(401);
        $this->postJson(route('webpush.unsubscribe'), [])->assertStatus(401);
        $this->postJson(route('webpush.test'))->assertStatus(401);
        $this->getJson(route('webpush.status'))->assertStatus(401);
    }

    public function test_authenticated_user_can_fetch_vapid_public_key(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->getJson(route('webpush.vapid_public_key'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['public_key']);
        $this->assertNotEmpty($response->json('public_key'));
    }

    public function test_user_can_subscribe_to_push_notifications(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $subscriptionData = [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint-' . uniqid(),
            'keys' => [
                'p256dh' => 'BDm01xTestPublicKeyHere...',
                'auth' => 'AuthSecretKeyTest...',
            ],
            'content_encoding' => 'aes128gcm',
        ];

        $response = $this->actingAs($user)->postJson(route('webpush.subscribe'), $subscriptionData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_type' => $user->getMorphClass(),
            'subscribable_id' => $user->id,
            'endpoint' => $subscriptionData['endpoint'],
            'public_key' => $subscriptionData['keys']['p256dh'],
            'auth_token' => $subscriptionData['keys']['auth'],
        ]);

        // Status check
        $statusResponse = $this->actingAs($user)->getJson(route('webpush.status'));
        $statusResponse->assertStatus(200);
        $statusResponse->assertJson([
            'has_subscription' => true,
            'count' => 1,
        ]);
    }

    public function test_user_can_unsubscribe_from_push_notifications(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-unsub-' . uniqid();

        $user->updatePushSubscription($endpoint, 'testkey', 'testtoken', 'aes128gcm');
        $this->assertEquals(1, $user->pushSubscriptions()->count());

        $response = $this->actingAs($user)->postJson(route('webpush.unsubscribe'), [
            'endpoint' => $endpoint,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => $endpoint,
        ]);

        $statusResponse = $this->actingAs($user)->getJson(route('webpush.status'));
        $statusResponse->assertJson([
            'has_subscription' => false,
            'count' => 0,
        ]);
    }

    public function test_test_endpoint_returns_error_when_no_subscription_exists(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->postJson(route('webpush.test'));

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_test_endpoint_sends_notification_when_subscription_exists(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'user']);
        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-test-call-' . uniqid();
        $user->updatePushSubscription($endpoint, 'testkey', 'testtoken', 'aes128gcm');

        $response = $this->actingAs($user)->postJson(route('webpush.test'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        Notification::assertSentTo($user, WebPushGenericNotification::class);
    }

    public function test_activity_logger_notify_user_dispatches_webpush(): void
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'user']);
        $endpoint = 'https://fcm.googleapis.com/fcm/send/user-notif-' . uniqid();
        $user->updatePushSubscription($endpoint, 'testkey', 'testtoken', 'aes128gcm');

        ActivityLogger::notifyUser(
            $user,
            'submission_approved',
            'Pengajuan Disetujui',
            'Berkas pendaftaran Anda telah disetujui oleh Kesbangpol.',
            '/history/1'
        );

        // Assert database notification is created
        $this->assertDatabaseHas('admin_notifications', [
            'user_id' => $user->id,
            'type' => 'submission_approved',
            'title' => 'Pengajuan Disetujui',
        ]);

        // Assert WebPush notification was dispatched to the user
        Notification::assertSentTo($user, WebPushGenericNotification::class, function ($notification) {
            return $notification->title === 'Pengajuan Disetujui';
        });
    }

    public function test_activity_logger_notify_admins_dispatches_webpush(): void
    {
        Notification::fake();

        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);

        $admin1->updatePushSubscription('https://fcm.googleapis.com/fcm/send/admin-1-' . uniqid(), 'k1', 't1', 'aes128gcm');
        $admin2->updatePushSubscription('https://fcm.googleapis.com/fcm/send/admin-2-' . uniqid(), 'k2', 't2', 'aes128gcm');

        ActivityLogger::notifyAdmins(
            'new_submission',
            'Berkas Baru Masuk',
            'Ormas Baru telah mengirimkan berkas pendaftaran.',
            '/admin/submissions'
        );

        $this->assertDatabaseHas('admin_notifications', [
            'user_id' => $admin1->id,
            'type' => 'new_submission',
        ]);

        $this->assertDatabaseHas('admin_notifications', [
            'user_id' => $admin2->id,
            'type' => 'new_submission',
        ]);

        Notification::assertSentTo($admin1, WebPushGenericNotification::class);
        Notification::assertSentTo($admin2, WebPushGenericNotification::class);
    }

    public function test_notification_pages_render_webpush_card_and_scripts(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        // User notifications page
        $userResponse = $this->actingAs($user)->get(route('user.notifications'));
        $userResponse->assertStatus(200);
        $userResponse->assertSee('Notifikasi Desktop (Web Push)');
        $userResponse->assertSee('Aktifkan Notifikasi Desktop');
        $userResponse->assertSee('vapid-public-key');
        $userResponse->assertSee('webpush.js');

        // Admin notifications page
        $adminResponse = $this->actingAs($admin)->get(route('admin.notifications'));
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Notifikasi Desktop (Web Push)');
        $adminResponse->assertSee('Aktifkan Notifikasi Desktop');
    }
}
