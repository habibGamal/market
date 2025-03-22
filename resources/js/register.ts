import axios from "axios";

export const subscribeUserToPush = async (registration: ServiceWorkerRegistration) => {
    if (!("Notification" in window)) {
        console.log("This browser does not support notifications.");
        return null;
    }

    const permission = await Notification.requestPermission();

    if (permission === "granted") {
        try {
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    import.meta.env.VITE_VAPID_PUBLIC_KEY
                ),
            });

            console.log("Push notification subscription obtained");
            return subscription;
        } catch (error) {
            console.error("Failed to subscribe to push notifications:", error);
            return null;
        }
    }
    return null;
};

export const registerSW = async () => {
    if (!("serviceWorker" in navigator)) return null;

    try {
        const registration = await navigator.serviceWorker.register(
            "/sw.js",
            {
                type: "module",
                scope: "/",
            }
        );
        console.log("ServiceWorker registered");

        const subscription = await subscribeUserToPush(registration);

        if (subscription) {
            try {
                const r = await navigator.serviceWorker.ready;
                try {
                    await (r as any).periodicSync.register("notify", {
                        minInterval: 5000,
                    });
                } catch (e) {
                    console.error("Error registering periodic sync", e);
                }

                await axios.post("/subscribe", subscription);
            } catch (error) {
                console.error("Error sending subscription to server:", error);
            }
        }

        return registration;
    } catch (error) {
        console.error("ServiceWorker registration failed:", error);
        return null;
    }
};

export function urlBase64ToUint8Array(base64String: string): Uint8Array {
    const padding = "=".repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/-/g, "+")
        .replace(/_/g, "/");

    const rawData = atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
