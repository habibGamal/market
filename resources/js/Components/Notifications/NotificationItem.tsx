import { cn } from "@/lib/utils";
import { ShoppingBag, Bell, PackageOpen, ClipboardList, TruckIcon } from "lucide-react";

interface NotificationItemProps {
    type: "order" | "delivery" | "offer" | "status" | "general";
    title: string;
    description: string;
    date: string;
    isRead: boolean;
    onClick?: () => void;
}

const iconMap = {
    order: ShoppingBag,
    delivery: TruckIcon,
    offer: PackageOpen,
    status: ClipboardList,
    general: Bell,
};

export function NotificationItem({
    type,
    title,
    description,
    date,
    isRead,
    onClick,
}: NotificationItemProps) {
    const Icon = iconMap[type];
    const formattedDate = new Intl.RelativeTimeFormat('ar', { numeric: 'auto' }).format(
        -Math.round((new Date().getTime() - new Date(date).getTime()) / (1000 * 60 * 60 * 24)),
        'day'
    );

    return (
        <div
            onClick={onClick}
            className={cn(
                "flex gap-4 p-4 transition-colors cursor-pointer",
                !isRead && "bg-primary-50/50",
                isRead && "hover:bg-secondary-50"
            )}
        >
            <div
                className={cn(
                    "flex h-12 w-12 shrink-0 items-center justify-center rounded-full",
                    !isRead ? "bg-primary-100" : "bg-secondary-100"
                )}
            >
                <Icon
                    className={cn(
                        "h-6 w-6",
                        !isRead ? "text-primary-600" : "text-secondary-600"
                    )}
                />
            </div>

            <div className="flex-1 min-w-0">
                <div className="flex items-start justify-between gap-2">
                    <h3
                        className={cn(
                            "font-medium line-clamp-1",
                            !isRead ? "text-primary-900" : "text-secondary-900"
                        )}
                    >
                        {title}
                    </h3>
                    <span className="text-xs text-secondary-500 whitespace-nowrap">
                        {formattedDate}
                    </span>
                </div>
                <p className="mt-1 text-sm text-secondary-600 line-clamp-2">
                    {description}
                </p>
            </div>
        </div>
    );
}
