import { Brand, Category } from "@/types";
import { router, usePage, useRemember } from "@inertiajs/react";
import axios from "axios";
import { useCallback, useEffect, useReducer, useRef, useState } from "react";
import { ProductFilters } from "./ProductFilters";
import { ProductsGrid } from "./ProductsGrid";
import { ProductsLoader } from "./ProductsLoader";
import { initialState, productsReducer } from "./productsReducer";

interface ProductsSectionProps {
    title: string;
    limit?: number | null;
    categories?: Category[];
    selectedCategories?: number[];
    brands?: Brand[];
    selectedBrands?: number[];
    sortBy?: string;
    sortDirection?: "asc" | "desc";
    minPrice?: number;
    maxPrice?: number;
    showFilters?: boolean;
    onlyDeals?: boolean;
    setQueryParams?: (params: URLSearchParams) => void;
}

export function ProductsSection({
    title,
    limit = 10,
    categories = [],
    selectedCategories: initialSelectedCategories = [],
    brands = [],
    selectedBrands: initialSelectedBrands = [],
    sortBy: initialSortBy = "created_at",
    sortDirection: initialSortDirection = "desc",
    minPrice,
    maxPrice,
    showFilters = true,
    onlyDeals = false,
    setQueryParams,
}: ProductsSectionProps) {
    const [state, dispatch] = useReducer(productsReducer, {
        ...initialState,
        selectedCategories: initialSelectedCategories,
        selectedBrands: initialSelectedBrands,
        sortBy: initialSortBy,
        sortDirection: initialSortDirection,
    });

    const loaderRef = useRef<HTMLDivElement>(null);

    const buildQueryParams = useCallback(
        (pageNum: number) => {
            const params = new URLSearchParams();

            if (pageNum) params.append("page", pageNum.toString());
            if (limit) params.append("limit", limit.toString());
            if (state.selectedCategories.length)
                params.append("categories", state.selectedCategories.join(","));
            if (state.selectedBrands.length)
                params.append("brands", state.selectedBrands.join(","));
            if (state.sortBy) params.append("sort_by", state.sortBy);
            if (state.sortDirection)
                params.append("sort_direction", state.sortDirection);
            if (minPrice) params.append("min_price", minPrice.toString());
            if (maxPrice) params.append("max_price", maxPrice.toString());
            if (onlyDeals) params.append("only_deals", "true");
            setQueryParams?.(params);
            return params.toString();
        },
        [
            state.selectedCategories,
            state.selectedBrands,
            state.sortBy,
            state.sortDirection,
            limit,
            minPrice,
            maxPrice,
            onlyDeals,
        ]
    );

    const loadMore = useCallback(async () => {
        if (state.loading || !state.hasMore) return;

        dispatch({ type: "SET_LOADING", payload: true });
        try {
            const queryParams = buildQueryParams(state.page + 1);
            const response = await axios.get(`/api/products?${queryParams}`);
            const newProducts = response.data.data;

            if (
                newProducts.length === 0 ||
                (limit && state.products.length >= limit)
            ) {
                dispatch({ type: "SET_HAS_MORE", payload: false });
            } else {
                dispatch({ type: "APPEND_PRODUCTS", payload: newProducts });
                dispatch({ type: "SET_PAGE", payload: state.page + 1 });
            }
        } catch (error) {
            console.error("Error loading more products:", error);
        } finally {
            dispatch({ type: "SET_LOADING", payload: false });
        }
    }, [state.loading, state.hasMore, state.page, buildQueryParams]);

    const loadInitialProducts = useCallback(async () => {
        dispatch({ type: "SET_LOADING", payload: true });
        try {
            const queryParams = buildQueryParams(1);
            const response = await axios.get(`/api/products?${queryParams}`);
            const newProducts = response.data.data;
            dispatch({ type: "SET_PRODUCTS", payload: newProducts });
            dispatch({
                type: "SET_HAS_MORE",
                payload: response.data.total > newProducts.length,
            });
            dispatch({ type: "SET_PAGE", payload: 1 });
        } catch (error) {
            console.error("Error loading initial products:", error);
        } finally {
            dispatch({ type: "SET_LOADING", payload: false });
        }
    }, [buildQueryParams]);

    const handleShowAll = useCallback(() => {
        const queryParams = new URLSearchParams();
        if (state.selectedCategories.length)
            queryParams.append(
                "categories",
                state.selectedCategories.join(",")
            );
        if (state.selectedBrands.length)
            queryParams.append("brands", state.selectedBrands.join(","));
        if (state.sortBy) queryParams.append("sort_by", state.sortBy);
        if (state.sortDirection)
            queryParams.append("sort_direction", state.sortDirection);
        if (minPrice) queryParams.append("min_price", minPrice.toString());
        if (maxPrice) queryParams.append("max_price", maxPrice.toString());
        if (onlyDeals) queryParams.append("only_deals", "true");

        router.visit(`/products?${queryParams.toString()}`);
    }, [
        state.selectedCategories,
        state.selectedBrands,
        state.sortBy,
        state.sortDirection,
        minPrice,
        maxPrice,
        onlyDeals,
    ]);

    useEffect(() => {
        loadInitialProducts();
    }, [
        state.selectedCategories,
        state.selectedBrands,
        state.sortBy,
        state.sortDirection,
        onlyDeals,
    ]);

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    loadMore();
                }
            },
            { threshold: 0.1 }
        );

        if (loaderRef.current) {
            observer.observe(loaderRef.current);
        }

        return () => observer.disconnect();
    }, [loadMore, limit]);
    return (
        <section className="mb-8">
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <h2 className="text-xl font-bold">{title}</h2>
                {showFilters && (
                    <ProductFilters
                        categories={categories}
                        brands={brands}
                        selectedCategories={state.selectedCategories}
                        selectedBrands={state.selectedBrands}
                        sortBy={state.sortBy}
                        sortDirection={state.sortDirection}
                        dispatch={dispatch}
                        limit={limit}
                        onShowAll={handleShowAll}
                    />
                )}
            </div>

            <ProductsGrid products={state.products} />

            <ProductsLoader
                loading={state.loading}
                hasMore={state.hasMore}
                hasProducts={state.products.length > 0}
                loaderRef={loaderRef}
                hasLimit={limit !== null}
            />
        </section>
    );
}
