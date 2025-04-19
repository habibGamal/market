// Service Worker Lifecycle Events
const CACHE_NAME = "offline-v2";
const OFFLINE_URL = "/offline";

self.addEventListener("install", function (event) {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then(function (cache) {
                return cache.add(new Request(OFFLINE_URL, { cache: "reload" }));
            })
            .then(function () {
                return self.skipWaiting();
            })
    );
});

self.addEventListener("activate", function (event) {
    // Clean up old caches
    event.waitUntil(
        caches
            .keys()
            .then(function (cacheNames) {
                return Promise.all(
                    cacheNames
                        .filter(function (cacheName) {
                            return cacheName !== CACHE_NAME;
                        })
                        .map(function (cacheName) {
                            return caches.delete(cacheName);
                        })
                );
            })
            .then(function () {
                return self.clients.claim();
            })
    );
});

// Fetch Event Handling
self.addEventListener("fetch", function (event) {
    if (["navigate", "cors"].includes(event.request.mode)) {
        event.respondWith(
            fetch(event.request).catch(function () {
                // When offline, return the offline page from cache
                return caches.open(CACHE_NAME).then(function (cache) {
                    if (event.request.destination === "document")
                        return caches.match(OFFLINE_URL);
                    throw err;
                });
            })
        );
        return;
    }

    // For non-navigation requests, fall back to network only
    event.respondWith(
        fetch(event.request).catch(function () {
            // Return nothing if it fails - will show as failed resources
            return new Response(null, { status: 504 });
        })
    );
});

// Push Event Handling
self.addEventListener("push", function (event) {
    var _a;
    console.log("Push Event Received", event);
    try {
        var data =
            (_a = event.data) === null || _a === void 0 ? void 0 : _a.json();
        var title = data.title;
        var options = {
            body: data.body,
            badge: "/icon512_rounded.png",
            icon: "/icon512_rounded.png",
            lang: "ar",
            silent: false,
        };
        event.waitUntil(self.registration.showNotification(title, options));
    } catch (e) {
        console.error("Error parsing push notification data", e);
        var title = "New Notification";
        var options = {
            body: "You have new updates!",
            icon: "/icon.png",
            badge: "/badge.png",
        };
        event.waitUntil(self.registration.showNotification(title, options));
    }
});
self.addEventListener("periodicsync", function (event) {
    console.log("Periodic Sync Event Received", event);
    const today = new Date();
    const isTuesday = today.getDay() === 2; // 0 is Sunday, 1 is Monday, 2 is Tuesday
    if (!isTuesday) {
        return;
    }
    if (event.tag === "notify") {
        event.waitUntil(
            self.registration.showNotification("مرحبا بك في سندباد!", {
                body: "نحن هنا لمساعدتك في كل ما تحتاجه يوميا",
                badge: "/icon512_rounded.png",
                icon: "/icon512_rounded.png",
            })
        );
    }
});
// Notification Click Handler
self.addEventListener("notificationclick", function (event) {
    event.notification.close();
    event.waitUntil(
        self.clients.matchAll({ type: "window" }).then(function (clients) {
            if (clients.length) {
                clients[0].focus();
            } else {
                self.clients.openWindow("/");
            }
        })
    );
});
