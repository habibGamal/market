import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Sheet, SheetContent, SheetTrigger } from "@/Components/ui/sheet";
import { SearchInput } from "@/Components/Search/SearchInput";
import {
    MenuIcon,
    Heart,
    ShoppingBag,
    RotateCcw,
    User,
    UserPlus,
    ChevronLeft,
    LogIn,
    LogOut,
    FileText,
    HelpCircle,
} from "lucide-react";
import { Link, usePage, router } from "@inertiajs/react";
import React from "react";

interface NavLink {
    href: string;
    label: string;
    icon: React.ReactNode;
    requiresAuth?: boolean;
    guestOnly?: boolean;
}

const navLinks: NavLink[] = [
    {
        href: "/favorites",
        label: "المفضلة",
        icon: <Heart className="h-5 w-5" />,
        requiresAuth: true,
    },
    {
        href: "/orders",
        label: "طلباتي",
        icon: <FileText className="h-5 w-5" />,
        requiresAuth: true,
    },
    {
        href: "/returns",
        label: "المرتجعات",
        icon: <RotateCcw className="h-5 w-5" />,
        requiresAuth: true,
    },
    {
        href: "/my-reports",
        label: "تقاريري",
        icon: <FileText className="h-5 w-5" />,
        requiresAuth: true,
    },
    {
        href: "/support",
        label: "الدعم والمساعدة",
        icon: <HelpCircle className="h-5 w-5" />,
    },
    {
        href: "/profile",
        label: "الملف الشخصي",
        icon: <User className="h-5 w-5" />,
        requiresAuth: true,
    },
    {
        href: "/register",
        label: "تسجيل حساب جديد",
        icon: <UserPlus className="h-5 w-5" />,
        guestOnly: true,
    },
    {
        href: "/login",
        label: "تسجيل الدخول",
        icon: <LogIn className="h-5 w-5" />,
        guestOnly: true,
    },
];

export function TopNavigation() {
    const { auth } = usePage().props;
    const [isOpen, setIsOpen] = React.useState(false);

    const handleLogout = () => {
        setIsOpen(false);
        router.post('/logout');
    };

    const handleLinkClick = () => {
        setIsOpen(false);
    };

    const filteredNavLinks = navLinks.filter(link => {
        if (auth.user) {
            return !link.guestOnly;
        }
        return !link.requiresAuth;
    });

    return (
        <div className="fixed top-0 left-0 right-0 z-50 bg-white border-b">
            <div className="container flex items-center justify-between p-4">
                {/* Logo */}
                <Link href="/" className="flex items-center">
                    <svg
                        width="60"
                        height="40"
                        viewBox="0 0 120 40"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-8"
                    >
                        <path
                            d="M20 10C14.4772 10 10 14.4772 10 20C10 25.5228 14.4772 30 20 30C25.5228 30 30 25.5228 30 20C30 14.4772 25.5228 10 20 10Z"
                            fill="#F4F1FF"
                        />
                        <path
                            d="M20 12C15.5817 12 12 15.5817 12 20C12 24.4183 15.5817 28 20 28C24.4183 28 28 24.4183 28 20C28 15.5817 24.4183 12 20 12Z"
                            fill="#6E56CF"
                        />
                        <path
                            d="M40 20C40 23.3137 37.3137 26 34 26H28.5306C30.6692 24.2267 32 21.26 32 18C32 14.74 30.6692 11.7733 28.5306 10H34C37.3137 10 40 12.6863 40 16V20Z"
                            fill="#6E56CF"
                        />
                        <path
                            d="M46 15H50.5L54 27H51L50.2 24H46.3L45.5 27H42.5L46 15ZM49.42 22L48.25 17.55L47.08 22H49.42Z"
                            fill="#1A1523"
                        />
                        <path
                            d="M55 15H58V24.5H62.5V27H55V15Z"
                            fill="#1A1523"
                        />
                        <path
                            d="M69.96 27.24C68.04 27.24 66.51 26.67 65.37 25.53C64.23 24.39 63.66 22.83 63.66 20.85V15H66.66V20.85C66.66 21.93 66.94 22.77 67.5 23.37C68.06 23.95 68.88 24.24 69.96 24.24C71.02 24.24 71.84 23.94 72.42 23.34C73.02 22.74 73.32 21.91 73.32 20.85V15H76.32V20.85C76.32 22.83 75.75 24.39 74.61 25.53C73.47 26.67 71.9 27.24 69.96 27.24Z"
                            fill="#1A1523"
                        />
                        <path
                            d="M78.96 15H82.5L87.75 22.44V15H90.75V27H87.21L81.96 19.56V27H78.96V15Z"
                            fill="#1A1523"
                        />
                    </svg>
                </Link>

                {/* Search */}
                <div className="w-full mx-4">
                    <SearchInput />
                </div>

                {/* Menu */}
                <Sheet open={isOpen} onOpenChange={setIsOpen}>
                    <SheetTrigger asChild>
                        <Button variant="ghost" size="icon">
                            <MenuIcon className="w-6 h-6" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent
                        side="right"
                        className="w-[300px] sm:w-[400px] p-0"
                    >
                        <nav className="border-r h-full">
                            <div className="py-6 px-4 bg-primary-50">
                                <h2 className="text-lg font-semibold text-primary-900">
                                    القائمة الرئيسية
                                </h2>
                            </div>
                            <div className="py-4">
                                {filteredNavLinks.map((link) => (
                                    <Link
                                        key={link.href}
                                        href={link.href}
                                        onClick={handleLinkClick}
                                        className="flex items-center justify-between px-4 py-3 text-secondary-900 hover:bg-secondary-50 transition-colors group"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 rounded-full bg-primary-100 text-primary-700 group-hover:bg-primary-200 transition-colors">
                                                {link.icon}
                                            </div>
                                            <span className="text-lg">
                                                {link.label}
                                            </span>
                                        </div>
                                    </Link>
                                ))}
                                {auth.user && (
                                    <button
                                        onClick={handleLogout}
                                        className="w-full flex items-center justify-between px-4 py-3 text-secondary-900 hover:bg-secondary-50 transition-colors group"
                                    >
                                        <div className="flex items-center gap-3">
                                            <div className="p-2 rounded-full bg-primary-100 text-primary-700 group-hover:bg-primary-200 transition-colors">
                                                <LogOut className="h-5 w-5" />
                                            </div>
                                            <span className="text-lg">
                                                تسجيل الخروج
                                            </span>
                                        </div>
                                        <ChevronLeft className="w-5 h-5 text-secondary-400 group-hover:text-primary-500 transition-colors" />
                                    </button>
                                )}
                            </div>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>
        </div>
    );
}
