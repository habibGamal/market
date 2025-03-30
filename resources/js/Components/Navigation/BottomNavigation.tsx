import { Link, usePage } from "@inertiajs/react";
import { cn } from "@/lib/utils";
import { Compass, Grid2X2, Zap, ShoppingCart, Bell } from "lucide-react";
import { PageProps } from "@/types";

interface NavItem {
    href: string;
    icon: React.ReactNode;
    label: string;
    count?: number;
}

interface AdditionalPageProps extends PageProps {
    cartCount: number;
    notificationsCount: number;
}

export function BottomNavigation() {
    const { url, props } = usePage<AdditionalPageProps>();
    const { auth, cartCount, notificationsCount } = props;
    const isAuthenticated = !!auth.user;

    const navItems: NavItem[] = [
        {
            href: "/",
            icon: <Compass className="h-6 w-6" />,
            label: "استكشف",
        },
        {
            href: "/categories",
            icon: <Grid2X2 className="h-6 w-6" />,
            label: "الفئات",
        },
        {
            href: "/hot-deals",
            icon: <Zap className="h-6 w-6" />,
            label: "العروض",
        },
        {
            href: "/cart",
            icon: <ShoppingCart className="h-6 w-6" />,
            label: "السلة",
            count: isAuthenticated ? cartCount : undefined,
        },
        {
            href: "/notifications",
            icon: <Bell className="h-6 w-6" />,
            label: "التنبيهات",
            count: isAuthenticated ? notificationsCount : undefined,
        },
    ];

    return (
        <nav className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t">
            <div className="grid grid-cols-5 gap-1">
                {navItems.map((item) => {
                    const isActive = item.href == url;
                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={cn(
                                "flex flex-col items-center justify-center py-2 text-xs transition-colors",
                                isActive
                                    ? "text-primary-500 font-medium"
                                    : "text-secondary-600 hover:text-primary-500"
                            )}
                        >
                            <div
                                className={cn(
                                    "relative",
                                    isActive &&
                                        "after:absolute after:-bottom-1 after:left-1/2 after:-translate-x-1/2 after:w-1 after:h-1 after:bg-primary-500 after:rounded-full"
                                )}
                            >
                                {item.icon}
                                {item.count && item.count > 0 ? (
                                    <span className="absolute -top-2 -left-2 flex items-center justify-center min-w-[18px] h-[18px] text-[10px] bg-red-500 text-white rounded-full px-1">
                                        {item.count > 99 ? "99+" : item.count}
                                    </span>
                                ) : null}
                            </div>
                            <span className="mt-1">{item.label}</span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
