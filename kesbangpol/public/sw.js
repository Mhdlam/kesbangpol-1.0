/**
 * Service Worker for Kesbangpol Web Push Notifications
 * Supports Microsoft Edge, Google Chrome, and modern desktop browsers on Windows 11
 */

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'Pemberitahuan Kesbangpol',
            body: event.data.text(),
            data: { url: '/' }
        };
    }

    const title = payload.title || 'Badan Kesbangpol Kota Palembang';
    const options = {
        body: payload.body || 'Anda menerima notifikasi baru dari sistem.',
        icon: payload.icon || '/images/notification-icon.png',
        badge: payload.badge || '/images/notification-badge.png',
        tag: payload.tag || 'kesbangpol-notification-' + Date.now(),
        renotify: payload.renotify !== undefined ? payload.renotify : true,
        requireInteraction: payload.requireInteraction !== undefined ? payload.requireInteraction : true,
        vibrate: payload.vibrate || [200, 100, 200],
        data: payload.data || { url: '/' }
    };

    if (payload.actions && Array.isArray(payload.actions)) {
        options.actions = payload.actions;
    } else {
        options.actions = [
            { action: 'open', title: 'Buka Halaman' },
            { action: 'close', title: 'Tutup' }
        ];
    }

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    let targetUrl = '/';
    if (event.notification.data && event.notification.data.url) {
        targetUrl = event.notification.data.url;
    }

    // Convert relative URL to absolute URL with current origin
    const absoluteTargetUrl = new URL(targetUrl, self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            // Check if there is already an open window
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if ('focus' in client && client.url.startsWith(self.location.origin)) {
                    if ('navigate' in client) {
                        client.navigate(absoluteTargetUrl);
                    }
                    return client.focus();
                }
            }
            // If no window is open, open a new window
            if (clients.openWindow) {
                return clients.openWindow(absoluteTargetUrl);
            }
        })
    );
});
