import { Loader2 } from "lucide-react";

interface ProductsLoaderProps {
    loading: boolean;
    hasMore: boolean;
    hasProducts: boolean;
    hasLimit: boolean;
    loaderRef?: React.RefObject<HTMLDivElement>;
}

export function ProductsLoader({ loading, hasMore, hasProducts,hasLimit, loaderRef }: ProductsLoaderProps) {

    return (
        <div
            ref={loaderRef}
            className="w-full py-4 flex justify-center"
        >
            {loading && (
                <Loader2 className="h-6 w-6 animate-spin text-gray-500" />
            )}
            {!hasMore && !hasLimit && hasProducts && (
                <p className="text-gray-500 text-sm">لا توجد منتجات أخرى</p>
            )}
        </div>
    );
}
