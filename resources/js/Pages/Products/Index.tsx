import { Head, router } from "@inertiajs/react";
import { ProductsHorizontalSection } from "@/Components/Products/ProductsSection";
import { useEffect, useState } from "react";

export default function Index() {
    // Get URL parameters
    const params = new URLSearchParams(window.location.search);

    // Parse categories and brands from comma-separated strings
    const categories =
        params.get("categories")?.split(",").map(Number).filter(Boolean) || [];
    const brands =
        params.get("brands")?.split(",").map(Number).filter(Boolean) || [];

    // Get sorting parameters
    const sortBy = params.get("sort_by") || "created_at";
    const sortDirection = (params.get("sort_direction") || "desc") as
        | "asc"
        | "desc";

    // Get price range
    const minPrice = Number(params.get("min_price")) || undefined;
    const maxPrice = Number(params.get("max_price")) || undefined;
    const page = Number(params.get("page")) || 1;

    const setQueryParams = (params: URLSearchParams) => {
        try {
            router.replace({
                url: window.location.pathname + "?" + params.toString(),
                preserveState: true,
                preserveScroll: true,
                // clearHistory: true,
            });
        } catch (e) {
            console.log(e);
        }
    };

    return (
        <>
            <Head title="المنتجات" />

            <ProductsHorizontalSection
                title="كل المنتجات"
                limit={null}
                selectedCategories={categories}
                selectedBrands={brands}
                sortBy={sortBy}
                sortDirection={sortDirection}
                minPrice={minPrice}
                maxPrice={maxPrice}
                showFilters={true}
                setQueryParams={setQueryParams}
            />
        </>
    );
}
