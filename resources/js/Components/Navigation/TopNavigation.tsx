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
        href: "/cart",
        label: "سلة التسوق",
        icon: <ShoppingBag className="h-5 w-5" />,
        requiresAuth: true,
    },
    {
        href: "/returns",
        label: "المرتجعات",
        icon: <RotateCcw className="h-5 w-5" />,
        requiresAuth: true,
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
                <Link href="/" className="text-xl font-bold text-primary-500">
                    متجر
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
