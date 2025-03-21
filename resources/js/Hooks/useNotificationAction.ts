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
            // Mark as read if not already read
            await axios.post(`/notifications/${id}/read`);
            if (onRead) onRead(id);

            // Track click
            await axios.post(`/notifications/${id}/track-click`);

            // Navigate based on notification type and data
            if (data?.url) {
                router.visit(data.url);
            } else if (data?.action_url) {
                router.visit(data.action_url);
            } else if (data?.order_id) {
                router.visit(`/orders/${data.order_id}`);
            }
        } catch (error) {
            console.error("Error handling notification:", error);
        }
    };

    return { handleNotificationClick };
}
