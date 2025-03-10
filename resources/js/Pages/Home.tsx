import { ImageSlider } from "@/Components/ImageSlider/ImageSlider";
import { CategoryCarousel } from "@/Components/Categories/CategoryCarousel";
import { ProductsHorizontalSection } from "@/Components/Products/ProductsSection";
import { AnnouncementsSection } from "@/Components/Announcements/AnnouncementsSection";
import { HomeProps } from "@/types";
import { router, usePage, useRemember, WhenVisible } from "@inertiajs/react";
import { useEffect } from "react";
import { Skeleton } from "@/Components/ui/skeleton";
import { PaginationLoadMore } from "@/Components/Products/PaginationLoadMore";
import { toast } from "sonner";

export default function Home({
    sliderImages,
    sections,
    pagination,
    categories,
    products,
    announcements,
}: HomeProps) {
    return (
        <div className="animate-section-x">
            <section className="mb-8 ltr">
                <ImageSlider images={sliderImages} />
            </section>

            {announcements?.length > 0 && (
                <section className="mb-8">
                    <AnnouncementsSection announcements={announcements} />
                </section>
            )}

            <section className="mb-8">
                <CategoryCarousel categories={categories} />
            </section>

            {sections.map((section) => (
                <ProductsHorizontalSection key={section.id} section={section} />
            ))}
            <PaginationLoadMore
                dataKey={"sections"}
                paginationKey={"pagination"}
                sectionKey={"page"}
                currentPage={pagination.current_page}
                nextPageUrl={pagination.next_page_url}
                total={pagination.total}
                LoadingSkeleton={() => (
                    <div className="w-full h-[200px] space-x-4">
                        <Skeleton className="w-full h-[60%] rounded-lg" />
                        <Skeleton className="w-full h-[30%] rounded-lg" />
                    </div>
                )}
            />
        </div>
    );
}
