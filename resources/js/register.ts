import axios from "axios";

export const registerSW = () => {
    if ("serviceWorker" in navigator) {
        window.addEventListener("load", async () => {
            try {
                const registration = await navigator.serviceWorker.register(
                    "/sw.js",
                    {
                        type: "module",
                        scope: "/",
                    }
                );
                console.log("ServiceWorker registered");
                if (!("Notification" in window)) {
                    console.log("This browser does not support notifications.");
                    return;
                }
                // Request permission for push notifications
                const permission = await Notification.requestPermission();

                if (permission === "granted") {
                    const r = await navigator.serviceWorker.ready;

                    const subscription =
                        await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: urlBase64ToUint8Array(
                                "BMPu9W4TkXuCKiAEmN5N-iTthZEPhMy67-CahchBHXolnLNKAmVZCedcRHw2jTyQ_dzoUc9VTr1WtezBNYxC2_c"
                            ),
                        });

                    console.log(
                        "Push notification subscription:",
                        subscription,
                        subscription.getKey('p256dh'),
                        subscription.getKey('auth')
                    );

                    try {
                        console.log((r as any).periodicSync);
                        await (r as any).periodicSync.register("notify", {
                            minInterval: 5000,
                        });
                    } catch (e) {
                        console.error("Error registering periodic sync", e);
                    }

                    // Send subscription to your server
                    await axios.post("/subscribe", subscription);
                }
            } catch (error) {
                console.error("ServiceWorker registration failed:", error);
            }
        });
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
