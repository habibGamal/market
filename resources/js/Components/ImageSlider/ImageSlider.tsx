import { useState, useEffect } from "react";
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/Components/ui/carousel";
import { cn } from "@/lib/utils";
import Autoplay from "embla-carousel-autoplay";
import { FallbackImage } from "../ui/fallback-image";

interface ImageSliderProps {
    images: Array<{
        src: string;
        alt: string;
        href?: string;
    }>;
    className?: string;
}

export function ImageSlider({ images, className }: ImageSliderProps) {
    return (
        <Carousel
            opts={{
                loop: true,
            }}
            plugins={[
                Autoplay({
                    delay: 4000,
                }),
            ]}
            className={cn("w-full", className)}
        >
            <CarouselContent>
                {images.map((image, index) => (
                    <CarouselItem key={index}>
                        <div className="relative aspect-[2/1] w-full overflow-hidden rounded-lg">
                            <FallbackImage
                                src={image.src}
                                alt={image.alt}
                                className="object-cover w-full h-full"
                            />
                        </div>
                    </CarouselItem>
                ))}
            </CarouselContent>
            <CarouselPrevious className="hidden sm:flex" />
            <CarouselNext className="hidden sm:flex" />
        </Carousel>
    );
}
