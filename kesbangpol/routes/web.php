<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\DocumentController;
use App\Http\Controllers\User\OrganizationFormController;
use App\Http\Controllers\User\SubmissionController as UserSubmissionController;
use App\Http\Controllers\User\HistoryController;
use App\Http\Controllers\User\ProfileController as UserProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Admin\SubmissionReviewController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\ActivityLogController as AdminActivityLogController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;

use App\Http\Controllers\User\ChatController as UserChatController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\WebPushController;

Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('user.dashboard');
    }
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Shared secure file, chat & Web Push routes
Route::middleware('auth')->group(function () {
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('document.download');
    Route::get('/chat/attachment/{message}', [UserChatController::class, 'downloadAttachment'])->name('chat.download_attachment');

    // Web Push Notification API
    Route::get('/api/webpush/vapid-public-key', [WebPushController::class, 'vapidPublicKey'])->name('webpush.vapid_public_key');
    Route::post('/api/webpush/subscribe', [WebPushController::class, 'subscribe'])->name('webpush.subscribe');
    Route::post('/api/webpush/unsubscribe', [WebPushController::class, 'unsubscribe'])->name('webpush.unsubscribe');
    Route::post('/api/webpush/test', [WebPushController::class, 'test'])->name('webpush.test');
    Route::get('/api/webpush/status', [WebPushController::class, 'status'])->name('webpush.status');
});

// User routes
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::post('/dashboard/religion', [UserDashboardController::class, 'updateReligion'])->name('user.religion.update');
    
    Route::get('/form-ormas', [OrganizationFormController::class, 'show'])->name('user.form_ormas');
    Route::post('/form-ormas', [OrganizationFormController::class, 'store'])->name('user.form_ormas.store');
    Route::post('/form-ormas/draft', [OrganizationFormController::class, 'saveDraft'])->name('user.form_ormas.draft');
    Route::get('/form-ormas/download-excel/{form}', [OrganizationFormController::class, 'downloadExcel'])->name('user.form_ormas.download_excel');
    
    Route::post('/document/upload', [DocumentController::class, 'upload'])->name('user.document.upload');
    Route::delete('/documents/delete-all', [DocumentController::class, 'deleteAll'])->name('user.document.delete_all');
    Route::delete('/document/{document}', [DocumentController::class, 'delete'])->name('user.document.delete');
    
    Route::post('/submission/send', [UserSubmissionController::class, 'submit'])->name('user.submission.send');
    
    Route::get('/history', [HistoryController::class, 'index'])->name('user.history');
    Route::get('/history/{submission}', [HistoryController::class, 'show'])->name('user.history.detail');
    Route::post('/history/{submission}/start-revision', [HistoryController::class, 'startRevision'])->name('user.submission.start_revision');
    
    Route::get('/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::post('/profile', [UserProfileController::class, 'update'])->name('user.profile.update');

    // Notifications
    Route::get('/notifications', [UserNotificationController::class, 'index'])->name('user.notifications');
    Route::post('/notifications/{notification}/read', [UserNotificationController::class, 'markAsRead'])->name('user.notifications.read');
    Route::post('/notifications/read-all', [UserNotificationController::class, 'markAllAsRead'])->name('user.notifications.read_all');
    Route::get('/api/user/notifications/unread', [UserNotificationController::class, 'fetchUnread'])->name('user.notifications.fetch_unread');

    // Chat & Consultation
    Route::get('/chat', [UserChatController::class, 'index'])->name('user.chat');
    Route::post('/chat', [UserChatController::class, 'store'])->name('user.chat.store');
    Route::get('/api/user/chat/messages', [UserChatController::class, 'fetchMessages'])->name('user.chat.fetch_messages');
    Route::get('/api/user/chat/unread-count', [UserChatController::class, 'fetchUnreadCount'])->name('user.chat.unread_count');
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // 2. Berkas Masuk (Submissions Inbox)
    Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('admin.submissions.index');
    Route::post('/submissions/bulk-action', [AdminSubmissionController::class, 'bulkAction'])->name('admin.submissions.bulk_action');

    // 3. Document Review Workspace & Submission Processing
    Route::get('/submissions/{submission}', [SubmissionReviewController::class, 'show'])->name('admin.submissions.show');
    Route::get('/submissions/{submission}/download-all', [SubmissionReviewController::class, 'downloadAll'])->name('admin.submissions.download_all');
    Route::post('/submissions/{submission}/review-doc', [SubmissionReviewController::class, 'reviewDocument'])->name('admin.submissions.review_doc');
    Route::post('/submissions/{submission}/request-revision', [SubmissionReviewController::class, 'requestRevision'])->name('admin.submissions.request_revision');
    Route::post('/submissions/{submission}/approve', [SubmissionReviewController::class, 'approve'])->name('admin.submissions.approve');
    Route::post('/submissions/{submission}/reject', [SubmissionReviewController::class, 'reject'])->name('admin.submissions.reject');
    Route::post('/submissions/{submission}/assign', [SubmissionReviewController::class, 'assignAdmin'])->name('admin.submissions.assign');
    Route::post('/submissions/{submission}/internal-notes', [SubmissionReviewController::class, 'storeInternalNote'])->name('admin.submissions.internal_notes');

    // 4. Organization Central Database & Archiving
    Route::get('/organizations', [AdminOrganizationController::class, 'index'])->name('admin.organizations');
    Route::get('/organizations/{organization}', [AdminOrganizationController::class, 'show'])->name('admin.organizations.show');
    Route::post('/organizations/{organization}/status', [AdminOrganizationController::class, 'updateStatus'])->name('admin.organizations.update_status');
    Route::post('/organizations/{organization}/internal-notes', [AdminOrganizationController::class, 'storeInternalNote'])->name('admin.organizations.internal_notes');

    // 5. User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');
    Route::post('/users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('admin.users.toggle_status');
    Route::post('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.update_role');
    Route::post('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset_password');

    // 6. Admin Notification Center & Broadcast
    Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications');
    Route::post('/notifications/{notification}/read', [AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.read');
    Route::post('/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.read_all');
    Route::post('/notifications/broadcast', [AdminChatController::class, 'sendBroadcastNotification'])->name('admin.notifications.broadcast');

    // 7. System Activity Logs
    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('admin.activity_logs');

    // 8. Administrative Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports');

    // 9. Interactive Chat Center
    Route::get('/chat', [AdminChatController::class, 'index'])->name('admin.chat.index');
    Route::get('/chat/{organization}', [AdminChatController::class, 'show'])->name('admin.chat.show');
    Route::post('/chat/{organization}', [AdminChatController::class, 'store'])->name('admin.chat.store');
    Route::get('/api/admin/chat/{organization}/messages', [AdminChatController::class, 'fetchMessages'])->name('admin.chat.fetch_messages');
    Route::get('/api/admin/chat/unread-count', [AdminChatController::class, 'fetchUnreadCount'])->name('admin.chat.unread_count');

    // 10. Profile
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('admin.profile');
    Route::post('/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');
});

