/// <reference lib="webworker" />
// self.addEventListener("push", (event: PushEvent) => {
//     // Parse the push payload with type safety
//     const payload: PushNotificationPayload = event.data?.json() || {
//         title: "New Notification",
//     };
//     event.waitUntil(
//         self.registration.showNotification(payload.title, {
//             body: payload.body,
//             icon: payload.icon || "/logo192.png", // Default icon
//         })
//     );
// });
// Service Worker Lifecycle Events
self.addEventListener("install", function (event) {
    event.waitUntil(caches
        .open("v1")
        .then(function (cache) {
        return cache.addAll([]);
    })
        .then(function () { return self.skipWaiting(); }));
});
self.addEventListener("activate", function (event) {
    event.waitUntil(self.clients.claim());
});
// Fetch Event Handling
self.addEventListener("fetch", function (event) {
    event.respondWith(caches.match(event.request).then(function (response) {
        return response || fetch(event.request);
    }));
});
// Push Event Handling
self.addEventListener("push", function (event) {
    var _a;
    console.log("Push Event Received", event);
    try {
        var data = (_a = event.data) === null || _a === void 0 ? void 0 : _a.json();
        var title = data.title;
        var options = {
            body: data.body,
            badge: "/icon512_rounded.png",
            icon: "/icon512_rounded.png",
            lang: "ar",
        };
        event.waitUntil(self.registration.showNotification(title, options));
    }
    catch (e) {
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
    if (event.tag === "notify") {
        event.waitUntil(self.registration.showNotification("Wake Time !!!", {
            body: "Hi, Good Morning",
        }));
    }
});
// Notification Click Handler
self.addEventListener("notificationclick", function (event) {
    event.notification.close();
    event.waitUntil(self.clients.matchAll({ type: "window" }).then(function (clients) {
        if (clients.length) {
            clients[0].focus();
        }
        else {
            self.clients.openWindow("/");
        }
    }));
});
