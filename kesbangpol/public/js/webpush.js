/**
 * Web Push Notification Client Manager for Kesbangpol
 * Handles Service Worker registration, VAPID key encoding, PushManager subscription,
 * permission management, and communication with the Laravel backend.
 */

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

window.webPushManager = function () {
    return {
        isSupported: false,
        permission: 'default', // 'default', 'granted', 'denied'
        isSubscribed: false,
        isLoading: false,
        isTesting: false,
        statusMessage: '',
        statusType: 'info', // 'info', 'success', 'warning', 'error'
        vapidPublicKey: '',
        swRegistration: null,

        async init() {
            // Check browser capability
            if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
                this.isSupported = false;
                this.statusMessage = 'Browser Anda tidak mendukung Web Push Notification.';
                this.statusType = 'warning';
                return;
            }

            this.isSupported = true;
            this.permission = Notification.permission;

            // Retrieve VAPID public key from meta tag
            const metaVapid = document.querySelector('meta[name="vapid-public-key"]');
            if (metaVapid && metaVapid.content) {
                this.vapidPublicKey = metaVapid.content;
            }

            try {
                // Register service worker
                this.swRegistration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                await navigator.serviceWorker.ready;

                // If VAPID key was not in meta tag, fetch from API
                if (!this.vapidPublicKey) {
                    const res = await fetch('/api/webpush/vapid-public-key', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.vapidPublicKey = data.public_key;
                    }
                }

                // Check existing subscription
                const subscription = await this.swRegistration.pushManager.getSubscription();
                if (subscription) {
                    this.isSubscribed = true;
                    // Sync with backend to ensure it's recorded
                    this.syncSubscriptionWithBackend(subscription);
                } else {
                    this.isSubscribed = false;
                }
            } catch (err) {
                console.error('[WebPush] Error initializing Service Worker:', err);
                this.statusMessage = 'Gagal mendaftarkan Service Worker: ' + (err.message || 'Error tidak diketahui');
                this.statusType = 'error';
            }
        },

        async toggleSubscription() {
            if (this.isLoading) return;

            if (this.isSubscribed) {
                await this.unsubscribe();
            } else {
                await this.subscribe();
            }
        },

        async subscribe() {
            if (!this.isSupported) {
                this.statusMessage = 'Browser tidak mendukung Web Push Notification.';
                this.statusType = 'warning';
                return;
            }

            this.isLoading = true;
            this.statusMessage = 'Meminta izin notifikasi browser...';
            this.statusType = 'info';

            try {
                // 1. Request user permission
                const permission = await Notification.requestPermission();
                this.permission = permission;

                if (permission !== 'granted') {
                    this.isLoading = false;
                    this.isSubscribed = false;
                    this.statusMessage = permission === 'denied'
                        ? 'Izin notifikasi ditolak oleh browser. Klik ikon gembok / setelan situs di address bar Edge untuk mengizinkan.'
                        : 'Izin notifikasi belum diberikan.';
                    this.statusType = 'warning';
                    return;
                }

                // 2. Ensure VAPID Public Key is available
                if (!this.vapidPublicKey) {
                    const res = await fetch('/api/webpush/vapid-public-key', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!res.ok) throw new Error('Gagal mengambil VAPID Public Key dari server.');
                    const data = await res.json();
                    this.vapidPublicKey = data.public_key;
                }

                // 3. Subscribe with PushManager
                const applicationServerKey = urlBase64ToUint8Array(this.vapidPublicKey);
                const subscription = await this.swRegistration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: applicationServerKey
                });

                // 4. Send subscription to Laravel backend
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/api/webpush/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(subscription.toJSON())
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || 'Gagal menyimpan subscription ke server.');
                }

                this.isSubscribed = true;
                this.statusMessage = 'Notifikasi Desktop berhasil diaktifkan!';
                this.statusType = 'success';
            } catch (err) {
                console.error('[WebPush] Subscribe error:', err);
                this.statusMessage = 'Gagal mengaktifkan notifikasi: ' + (err.message || 'Error tidak diketahui');
                this.statusType = 'error';
            } finally {
                this.isLoading = false;
            }
        },

        async unsubscribe() {
            this.isLoading = true;
            this.statusMessage = 'Menonaktifkan notifikasi desktop...';
            this.statusType = 'info';

            try {
                const subscription = await this.swRegistration.pushManager.getSubscription();
                if (subscription) {
                    const endpoint = subscription.endpoint;
                    // 1. Unsubscribe on browser
                    await subscription.unsubscribe();

                    // 2. Delete on backend
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    await fetch('/api/webpush/unsubscribe', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ endpoint: endpoint })
                    });
                }

                this.isSubscribed = false;
                this.statusMessage = 'Notifikasi desktop telah dinonaktifkan. Riwayat notifikasi di website tetap aman.';
                this.statusType = 'info';
            } catch (err) {
                console.error('[WebPush] Unsubscribe error:', err);
                this.statusMessage = 'Gagal menonaktifkan notifikasi: ' + (err.message || 'Error');
                this.statusType = 'error';
            } finally {
                this.isLoading = false;
            }
        },

        async syncSubscriptionWithBackend(subscription) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                await fetch('/api/webpush/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(subscription.toJSON())
                });
            } catch (e) {
                console.warn('[WebPush] Silent sync warning:', e);
            }
        },

        async testNotification() {
            if (!this.isSubscribed) {
                this.statusMessage = 'Silakan aktifkan notifikasi desktop terlebih dahulu sebelum menguji.';
                this.statusType = 'warning';
                return;
            }

            this.isTesting = true;
            this.statusMessage = 'Mengirim notifikasi uji coba dari server...';
            this.statusType = 'info';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/api/webpush/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    this.statusMessage = 'Notifikasi uji coba berhasil dikirim! Periksa pop-up di pojok kanan bawah layar Windows 11 Anda.';
                    this.statusType = 'success';
                } else {
                    this.statusMessage = 'Gagal mengirim notifikasi uji coba: ' + (data.message || 'Error server');
                    this.statusType = 'error';
                }
            } catch (err) {
                console.error('[WebPush] Test error:', err);
                this.statusMessage = 'Terjadi kesalahan saat menguji notifikasi.';
                this.statusType = 'error';
            } finally {
                this.isTesting = false;
            }
        }
    };
};
