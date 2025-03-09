import { useRef } from "react";
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/Components/ui/carousel";
import Autoplay from "embla-carousel-autoplay";

interface Announcement {
    id: number;
    text: string;
    color: string;
}

interface AnnouncementsSectionProps {
    announcements: Announcement[];
}

export function AnnouncementsSection({
    announcements,
}: AnnouncementsSectionProps) {
    if (!announcements.length) return null;

    const plugin = useRef(Autoplay({ delay: 5000, stopOnInteraction: true }));

    return (
        <div className="relative bg-white shadow-sm">
            <Carousel
                opts={{
                    align: "start",
                    loop: true,
                    direction: "rtl",
                }}
                plugins={[plugin.current]}
            >
                <CarouselContent>
                    {announcements.map((announcement) => (
                        <CarouselItem key={announcement.id}>
                            <div className="grid place-items-center h-full py-3 px-4 text-center">
                                <div
                                    style={{ color: announcement.color }}
                                    dangerouslySetInnerHTML={{
                                        __html: announcement.text,
                                    }}
                                />
                            </div>
                        </CarouselItem>
                    ))}
                </CarouselContent>
                {announcements.length > 1 && (
                    <>
                        <CarouselPrevious className="hidden sm:flex" />
                        <CarouselNext className="hidden sm:flex" />
                    </>
                )}
            </Carousel>
        </div>
    );
}
