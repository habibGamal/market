import { Head, router } from "@inertiajs/react";
import { useEffect, useState } from "react";
import { Pagination, Product } from "@/types";
import { PaginationLoadMore } from "@/Components/Products/PaginationLoadMore";
import { EmptyState } from "@/Components/EmptyState";
import { ProductCard } from "@/Components/Products/ProductCard";
import { Skeleton } from "@/Components/ui/skeleton";
import { PageTitle } from "@/Components/ui/page-title";
import { ProductFilter } from "@/Components/Products/ProductFilter";

interface SectionProps {
    title: string;
    products: Pagination<Product>["data"];
    pagination: Pagination<Product>["pagination"];
    categories?: Array<{ id: number; name: string }>;
    brands?: Array<{ id: number; name: string }>;
}

export default function Section({
    title,
    products,
    pagination,
    categories = [],
    brands = [],
}: SectionProps) {
    // Get URL parameters
    const params = new URLSearchParams(window.location.search);
    const [selectedCategories, setSelectedCategories] = useState<number[]>(
        params.get("categories")?.split(",").map(Number).filter(Boolean) || []
    );
    const [selectedBrands, setSelectedBrands] = useState<number[]>(
        params.get("brands")?.split(",").map(Number).filter(Boolean) || []
    );
    const [minPrice, setMinPrice] = useState<number | undefined>(
        params.get("min_price") ? Number(params.get("min_price")) : undefined
    );
    const [maxPrice, setMaxPrice] = useState<number | undefined>(
        params.get("max_price") ? Number(params.get("max_price")) : undefined
    );

    const handleFilter = (filters: {
        categories?: number[];
        brands?: number[];
        sortBy?: string;
        sortDirection?: "asc" | "desc";
        minPrice?: number;
        maxPrice?: number;
    }) => {
        const currentParams = new URLSearchParams(window.location.search);

        if (filters.categories !== undefined) {
            if (filters.categories.length > 0) {
                currentParams.set("categories", filters.categories.join(","));
            } else {
                currentParams.delete("categories");
            }
            setSelectedCategories(filters.categories);
        }

        if (filters.brands !== undefined) {
            if (filters.brands.length > 0) {
                currentParams.set("brands", filters.brands.join(","));
            } else {
                currentParams.delete("brands");
            }
            setSelectedBrands(filters.brands);
        }

        if (filters.minPrice !== undefined) {
            if (filters.minPrice > 0) {
                currentParams.set("min_price", filters.minPrice.toString());
            } else {
                currentParams.delete("min_price");
            }
            setMinPrice(filters.minPrice);
        }

        if (filters.maxPrice !== undefined) {
            if (filters.maxPrice > 0) {
                currentParams.set("max_price", filters.maxPrice.toString());
            } else {
                currentParams.delete("max_price");
            }
            setMaxPrice(filters.maxPrice);
        }

        if (filters.sortBy && filters.sortDirection) {
            currentParams.set("sort_by", filters.sortBy);
            currentParams.set("sort_direction", filters.sortDirection);
        }

        router.get(
            `${window.location.pathname}?${currentParams.toString()}`,
            {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    };

    const ProductCardSkeleton = () => (
        <div className="space-y-2 min-w-[200px] h-full">
            <Skeleton className="w-full h-[60%] rounded-lg" />
            <Skeleton className="w-full h-[15%] rounded-lg" />
            <Skeleton className="w-full h-[15%] rounded-lg" />
        </div>
    );

    return (
        <>
            <Head title="المنتجات" />
            <PageTitle>{title}</PageTitle>

            <ProductFilter
                categories={categories}
                brands={brands}
                selectedCategories={selectedCategories}
                selectedBrands={selectedBrands}
                initialMinPrice={params.get("min_price") || ""}
                initialMaxPrice={params.get("max_price") || ""}
                onFilter={handleFilter}
            />

            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                {products && products.length > 0 ? (
                    products.map((product) => (
                        <ProductCard key={product.id} product={product} />
                    ))
                ) : (
                    <div className="col-span-2">
                        <EmptyState
                            title="لا توجد منتجات"
                            description="لم يتم العثور على منتجات بالمواصفات المطلوبة"
                        />
                    </div>
                )}
            </div>

            {products && products.length > 0 && (
                <PaginationLoadMore
                    dataKey={"products"}
                    paginationKey={"pagination"}
                    sectionKey={"page"}
                    currentPage={pagination.current_page}
                    nextPageUrl={pagination.next_page_url}
                    total={pagination.total}
                    LoadingSkeleton={ProductCardSkeleton}
                />
            )}
        </>
    );
}
