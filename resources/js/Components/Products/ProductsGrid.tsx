import type { Product } from "@/types";
import { cn } from "@/lib/utils";
import { ProductCard } from "./ProductCard";

interface ProductsGridProps {
    products: Product[];
}

export function ProductsGrid({ products }: ProductsGridProps) {
    return (
        <div className={cn(
            "grid grid-cols-1 gap-4",
            "sm:grid-cols-2",
            "md:grid-cols-3",
            "lg:grid-cols-4",
            "xl:grid-cols-5"
        )}>
            {products.map((product) => (
                <ProductCard key={product.id} product={product} />
            ))}
        </div>
    );
}
