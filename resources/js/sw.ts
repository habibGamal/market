/// <reference lib="webworker" />

// Define the expected shape of the push payload
interface PushNotificationPayload {
    title: string;
    body?: string;
    icon?: string;
}

// Add type declarations for Service Worker environment
export type {};
declare const self: ServiceWorkerGlobalScope;

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
self.addEventListener("install", (event: ExtendableEvent) => {
    event.waitUntil(
        caches
            .open("v1")
            .then((cache) => {
                return cache.addAll(["/"]);
            })
            .then(() => self.skipWaiting())
    );
});

self.addEventListener("activate", (event: ExtendableEvent) => {
    event.waitUntil(self.clients.claim());
});

// Fetch Event Handling
self.addEventListener("fetch", (event: FetchEvent) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});

// Push Event Handling
self.addEventListener("push", (event: PushEvent) => {
    console.log("Push Event Received", event);
    try {
        const data = event.data?.json();
        const title = data.title;
        const options = {
            body: data.body,
            badge: "/icon512_rounded.png",
            icon: "/icon512_rounded.png",
            lang: "ar",
        };

        event.waitUntil(self.registration.showNotification(title, options));
    } catch (e) {
        console.error("Error parsing push notification data", e);
        const title = "New Notification";
        const options = {
            body: "You have new updates!",
            icon: "/icon.png",
            badge: "/badge.png",
        };

        event.waitUntil(self.registration.showNotification(title, options));
    }
});

self.addEventListener("periodicsync", (event) => {
    console.log("Periodic Sync Event Received", event);
    if ((event as any).tag === "notify") {
        (event as any).waitUntil(
            self.registration.showNotification("Wake Time !!!", {
                body: `Hi, Good Morning`,
            })
        );
    }
});

// Notification Click Handler
self.addEventListener("notificationclick", (event: NotificationEvent) => {
    event.notification.close();
    event.waitUntil(
        self.clients.matchAll({ type: "window" }).then((clients) => {
            if (clients.length) {
                clients[0].focus();
            } else {
                self.clients.openWindow("/");
            }
        })
    );
});
