import { PropsWithChildren, useLayoutEffect, useRef } from "react";
import { TopNavigation } from "@/Components/Navigation/TopNavigation";
import { BottomNavigation } from "@/Components/Navigation/BottomNavigation";
import { router } from "@inertiajs/react";

export function MainLayout({ children }: PropsWithChildren) {
    const section = useRef<HTMLDivElement>(null);
    useLayoutEffect(() => {


        router.on("start", (e) => {
            console.log("start", e, e.detail.visit.url.pathname);
            if (
                e.detail.visit.method !== "get" ||
                e.detail.visit.url.pathname === window.location.pathname
            )
                return;
            section.current?.classList.remove("section-loaded");
            section.current?.classList.add("section-go-away");
        });
        router.on("finish", (e) => {
            if (
                e.detail.visit.method !== "get"
            )
                return;
            section.current?.classList.remove("section-go-away");
            section.current?.classList.add("section-loaded");
        });
    }, []);
    return (
        <div className="min-h-screen bg-gray-50 pb-16">
            <TopNavigation />

            <main ref={section} className="container mx-auto px-4 pt-20">{children}</main>

            <BottomNavigation />
        </div>
    );
}
