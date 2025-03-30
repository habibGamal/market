import { useState } from "react";
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from "@/Components/ui/carousel";
import { Button } from "@/Components/ui/button";
import { Link } from "@inertiajs/react";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Category } from "@/types";

interface CategoryCarouselProps {
    categories: Category[];
}

export function CategoryCarousel({ categories }: CategoryCarouselProps) {
    return (
        <div className="relative">
            <div className="flex items-center justify-between mb-4">
                <h2 className="text-xl font-bold">الفئات</h2>
                <Link href="/categories">
                    <Button variant="link">عرض الكل</Button>
                </Link>
            </div>
            <div className="ltr">
                <Carousel
                    opts={{
                        align: "start",
                        loop: true,
                    }}
                    className="w-full"
                >
                    <CarouselContent className="-ml-2 md:-ml-4">
                        {categories.map((category) => (
                            <CarouselItem
                                key={category.id}
                                className="pl-2 md:pl-4 basis-1/3 md:basis-1/4 lg:basis-1/5"
                            >
                                <Link href={`/categories/${category.id}`}>
                                    <div className="text-center">
                                        <div className="relative aspect-square overflow-hidden rounded-lg mb-2">
                                            <FallbackImage
                                                src={category.image}
                                                alt={category.name}
                                                className="transition-transform hover:scale-110"
                                            />
                                        </div>
                                        <span className="text-sm font-medium text-secondary-900">
                                            {category.name}
                                        </span>
                                    </div>
                                </Link>
                            </CarouselItem>
                        ))}
                    </CarouselContent>
                    <CarouselPrevious className="hidden md:flex" />
                    <CarouselNext className="hidden md:flex" />
                </Carousel>
            </div>
        </div>
    );
}
