import { cn } from "@/lib/utils";
import { Pagination, Product, Section } from "@/types";
import { Link, usePage } from "@inertiajs/react";
import { EmptyState } from "../EmptyState";
import { ProductCard } from "./ProductCard";
import { PaginationLoadMore } from "./PaginationLoadMore";
import { Skeleton } from "../ui/skeleton";

interface ProductsSectionProps {
    section: Section;
    showFilters?: boolean;
}

export function ProductsHorizontalSection({ section }: ProductsSectionProps) {
    const page = usePage();
    const sectionKey = `section_${section.id}_products_page`;
    const paginationKey = `${sectionKey}_pagination`;
    const dataKey = `${sectionKey}_data`;
    const data = page.props[dataKey] as Product[] | undefined;
    const paginator = page.props[paginationKey] as
        | Pagination<Product>["pagination"]
        | undefined;

    const ProductCardSkeleton = () => (
        <div className="space-y-2 min-w-[200px] h-full">
            <Skeleton className="w-full h-[60%] rounded-lg" />
            <Skeleton className="w-full h-[15%] rounded-lg" />
            <Skeleton className="w-full h-[15%] rounded-lg" />
        </div>
    );

    return (
        <section
            className={cn(
                "mb-8 px-4 py-6 bg-white rounded-lg shadow-sm",
                "dark:bg-gray-800/60"
            )}
        >
            <div className="flex flex-row justify-between items-start gap-4 mb-6">
                <h2 className="text-xl font-bold">{section.title}</h2>
                <Link
                    href={`/product-list?id=${section.id}&model=section`}
                    className="text-primary hover:text-primary/80 text-sm font-medium"
                >
                    عرض الكل
                </Link>
            </div>
            <div className="flex flex-row flex-nowrap  gap-4 overflow-x-auto">
                {data &&
                    data?.map((product) => (
                        <div
                            key={product.id}
                            className="shrink-0 max-w-[200px]"
                        >
                            <ProductCard product={product} />
                        </div>
                    ))}
                {paginator && (
                    <PaginationLoadMore
                        dataKey={dataKey}
                        paginationKey={paginationKey}
                        sectionKey={sectionKey}
                        currentPage={paginator.current_page}
                        nextPageUrl={paginator.next_page_url}
                        total={paginator.total}
                        LoadingSkeleton={ProductCardSkeleton}
                    />
                )}
            </div>
        </section>
    );
}
