import { useState } from "react";
import { Head } from "@inertiajs/react";
import { NotificationItem } from "@/Components/Notifications/NotificationItem";
import { Bell } from "lucide-react";
import { Button } from "@/Components/ui/button";

// Fake notifications data for demo purposes
const fakeNotifications = [
    {
        id: 1,
        type: "order" as const,
        title: "تم تأكيد طلبك #1234",
        description: "تم تأكيد طلبك وجاري تجهيزه. سيتم إخطارك عندما يكون جاهزًا للتوصيل.",
        date: new Date(Date.now() - 1000 * 60 * 30).toISOString(), // 30 minutes ago
        isRead: false,
    },
    {
        id: 2,
        type: "delivery" as const,
        title: "طلبك في الطريق",
        description: "السائق في الطريق إليك. سيصل خلال 30-45 دقيقة.",
        date: new Date(Date.now() - 1000 * 60 * 60 * 2).toISOString(), // 2 hours ago
        isRead: false,
    },
    {
        id: 3,
        type: "offer" as const,
        title: "عرض خاص على منتجات الألبان",
        description: "استمتع بخصم 15% على جميع منتجات الألبان حتى نهاية الأسبوع!",
        date: new Date(Date.now() - 1000 * 60 * 60 * 24).toISOString(), // 1 day ago
        isRead: true,
    },
    {
        id: 4,
        type: "status" as const,
        title: "تم تسليم طلبك #1205",
        description: "نأمل أن تكون راضيًا عن خدمتنا. شكرًا لثقتك بنا!",
        date: new Date(Date.now() - 1000 * 60 * 60 * 24 * 2).toISOString(), // 2 days ago
        isRead: true,
    },
];

export default function Notifications() {
    const [notifications, setNotifications] = useState(fakeNotifications);

    const handleNotificationClick = (id: number) => {
        setNotifications(notifications.map(notification =>
            notification.id === id ? { ...notification, isRead: true } : notification
        ));
        // Here you would typically navigate to the relevant page based on notification type
        console.log("Clicked notification:", id);
    };

    const markAllAsRead = () => {
        setNotifications(notifications.map(notification => ({ ...notification, isRead: true })));
    };

    const unreadCount = notifications.filter(n => !n.isRead).length;

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
                        <h1 className="text-2xl font-bold text-secondary-900">الإشعارات</h1>
                        {unreadCount > 0 && (
                            <p className="mt-1 text-sm text-secondary-500">
                                لديك {unreadCount} {unreadCount === 1 ? 'إشعار جديد' : 'إشعارات جديدة'}
                            </p>
                        )}
                    </div>
                    {unreadCount > 0 && (
                        <Button
                            variant="outline"
                            onClick={markAllAsRead}
                        >
                            تحديد الكل كمقروء
                        </Button>
                    )}
                </div>

                {/* Notifications List */}
                <div className="bg-white rounded-lg shadow-sm divide-y">
                    {notifications.map((notification) => (
                        <NotificationItem
                            key={notification.id}
                            type={notification.type}
                            title={notification.title}
                            description={notification.description}
                            date={notification.date}
                            isRead={notification.isRead}
                            onClick={() => handleNotificationClick(notification.id)}
                        />
                    ))}
                </div>
            </div>
        </>
    );
}
