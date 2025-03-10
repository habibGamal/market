import { Head, usePage } from "@inertiajs/react";
import { ProductsHorizontalSection } from "@/Components/Products/ProductsSection";
import { PageTitle } from "@/Components/ui/page-title";
import { PaginationLoadMore } from "@/Components/Products/PaginationLoadMore";
import { Skeleton } from "@/Components/ui/skeleton";
import { Pagination, Section } from "@/types";
import { EmptyState } from "@/Components/EmptyState";

interface HotDealsProps {
    sections: Pagination<Section>["data"];
    pagination: Pagination<Section>["pagination"];
}

export default function HotDeals({ sections, pagination }: HotDealsProps) {
    return (
        <>
            <Head title="العروض المميزة" />

            <PageTitle>
                <span>🔥</span>
                <span>العروض المميزة</span>
            </PageTitle>

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
        </>
    );
}
