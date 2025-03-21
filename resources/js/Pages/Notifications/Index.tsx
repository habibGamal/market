import { useEffect, useState } from "react";
import { Head, router, usePage } from "@inertiajs/react";
import { NotificationItem } from "@/Components/Notifications/NotificationItem";
import { Bell } from "lucide-react";
import { Button } from "@/Components/ui/button";
import axios from "axios";
import { Notification, Pagination } from "@/types";
import { PaginationLoadMore } from "@/Components/Products/PaginationLoadMore";
import { Skeleton } from "@/Components/ui/skeleton";

interface Props {
    notifications: Pagination<Notification>["data"];
    pagination: Pagination<Notification>["pagination"];
}

export default function Notifications({
    notifications: initNotifications,
    pagination,
}: Props) {
    const [notifications, setNotifications] =
        useState<Notification[]>(initNotifications);
    const props = usePage().props;
    const handleNotificationRead = (id: string) => {
        // Update local state to mark notification as read
        setNotifications((prevNotifications) =>
            prevNotifications.map((notification) =>
                notification.id === id
                    ? { ...notification, isRead: true }
                    : notification
            )
        );
    };

    const markAllAsRead = async () => {
        try {
            await axios.post("/notifications/read-all");
            router.reload();
        } catch (error) {
            console.error("Error marking all notifications as read:", error);
        }
    };

    const unreadCount = notifications.filter((n) => !n.isRead).length;

    useEffect(() => {
        setNotifications(initNotifications);
    }, [initNotifications]);

    const CardSkeleton = () => (
        <div className="space-y-2 min-w-[200px] h-[80px] my-4 bg-white rounded-lg shadow-sm">
            <Skeleton className="w-full h-[100%] rounded-lg" />
        </div>
    );

    if (notifications.length === 0) {
        return (
            <>
                <Head title="الإشعارات" />
                <div className="container mx-auto px-4 py-16">
                    <div className="text-center">
                        <Bell className="mx-auto h-12 w-12 text-secondary-400" />
                        <h3 className="mt-2 text-lg font-medium text-secondary-900">
                            لا توجد إشعارات
                        </h3>
                        <p className="mt-1 text-sm text-secondary-500">
                            ستظهر هنا جميع التحديثات والإشعارات الهامة
                        </p>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="الإشعارات" />
            <div className="container mx-auto px-4 py-6">
                {/* Header */}
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-bold text-secondary-900">
                            الإشعارات
                        </h1>
                        {unreadCount > 0 && (
                            <p className="mt-1 text-sm text-secondary-500">
                                لديك {unreadCount}{" "}
                                {unreadCount === 1
                                    ? "إشعار جديد"
                                    : "إشعارات جديدة"}
                            </p>
                        )}
                    </div>
                    {unreadCount > 0 && (
                        <Button variant="outline" onClick={markAllAsRead}>
                            تحديد الكل كمقروء
                        </Button>
                    )}
                </div>

                {/* Notifications List */}
                <div className="bg-white rounded-lg shadow-sm divide-y">
                    {notifications.map((notification) => (
                        <NotificationItem
                            key={notification.id}
                            id={notification.id}
                            type={notification.type}
                            title={notification.title}
                            description={notification.description}
                            date={notification.date}
                            isRead={notification.isRead}
                            data={notification.data}
                            onRead={handleNotificationRead}
                        />
                    ))}
                </div>

                {/* Load More Pagination */}
                {notifications && notifications.length > 0 && (
                    <PaginationLoadMore
                        dataKey={"notifications"}
                        paginationKey={"pagination"}
                        sectionKey={"page"}
                        currentPage={pagination.current_page}
                        nextPageUrl={pagination.next_page_url}
                        total={pagination.total}
                        LoadingSkeleton={CardSkeleton}
                    />
                )}
            </div>
        </>
    );
}
