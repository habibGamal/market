import axios from "axios";
import { router } from "@inertiajs/react";
import { NotificationData } from "@/types";

/**
 * Custom hook for notification actions
 * Handles marking notifications as read and navigation
 */
export function useNotificationAction() {
    /**
     * Handle notification click action
     * @param id - The notification ID
     * @param data - The notification data
     * @param onRead - Optional callback when notification is marked as read
     */
    const handleNotificationClick = async (
        id: string,
        data?: NotificationData,
        onRead?: (id: string) => void
    ) => {
        try {
            await axios.post(`/notifications/${id}/read`);

            // Call the callback if provided
            if (onRead) {
                onRead(id);
            }

            // Navigate to relevant page if there's a URL in notification data
            const targetUrl = data?.action_url || data?.url;

            if (targetUrl) {
                router.visit(targetUrl);
            }
        } catch (error) {
            console.error("Error marking notification as read:", error);
        }
    };

    return { handleNotificationClick };
}
