import { Link, usePage } from "@inertiajs/react";
import { cn } from "@/lib/utils";
import { Compass, Grid2X2, Zap, ShoppingCart, Bell } from "lucide-react";

interface NavItem {
    href: string;
    icon: React.ReactNode;
    label: string;
}

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
    },
    {
        href: "/notifications",
        icon: <Bell className="h-6 w-6" />,
        label: "التنبيهات",
    },
];

export function BottomNavigation() {
    const { url } = usePage();

    return (
        <nav className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t">
            <div className="grid grid-cols-5 gap-1">
                {navItems.map((item) => {
                    console.log(url)
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
                            </div>
                            <span className="mt-1">{item.label}</span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
