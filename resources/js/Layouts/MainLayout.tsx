import { PropsWithChildren, useLayoutEffect, useRef } from "react";
import { TopNavigation } from "@/Components/Navigation/TopNavigation";
import { BottomNavigation } from "@/Components/Navigation/BottomNavigation";
import { router } from "@inertiajs/react";
import { Toaster } from "sonner";

export function MainLayout({ children }: PropsWithChildren) {
    const section = useRef<HTMLDivElement>(null);
    useLayoutEffect(() => {
        router.on("start", (e) => {
            console.log(
                "start",
                e.detail.visit.only.length !== 0,
                e.detail.visit.only
            );
            if (
                e.detail.visit.method !== "get" ||
                e.detail.visit.url.pathname === window.location.pathname ||
                e.detail.visit.only.length !== 0
            )
                return;
            section.current?.classList.remove("section-loaded");
            section.current?.classList.add("section-go-away");
        });
        router.on("finish", (e) => {
            if (
                e.detail.visit.method !== "get" ||
                e.detail.visit.only.length !== 0
            )
                return;
            section.current?.classList.remove("section-go-away");
            section.current?.classList.add("section-loaded");
        });
        window.addEventListener("popstate", () => {
            console.log(
                "popstate",
                window.history.state.documentScrollPosition.top
            );
            setTimeout(
                () =>
                    window.scrollTo({
                        top: window.history.state.documentScrollPosition.top,
                        behavior: "smooth",
                    }),
                100
            );
        });
    }, []);
    return (
        <div className="min-h-screen bg-gray-50 pb-16">
            <TopNavigation />

            <main  className="container mx-auto px-4 pt-20">
                <div ref={section} className="">

                {children}
                <Toaster />
                </div>
            </main>

            <BottomNavigation />
        </div>
    );
}
