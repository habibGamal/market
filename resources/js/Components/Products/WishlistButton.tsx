import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Heart } from "lucide-react";
import { router } from "@inertiajs/react";
import { Product } from "@/types";

interface WishlistButtonProps {
    product: Product;
}

export function WishlistButton({ product }: WishlistButtonProps) {
    const [isLoading, setIsLoading] = useState(false);

    const handleAddToWishlist = () => {
        setIsLoading(true);
        router.post(
            route("wishlist.store"),
            { product_id: product.id },
            {
                onFinish: () => setIsLoading(false),
            }
        );
    };

    const handleRemoveFromWishlist = () => {
        setIsLoading(true);
        router.delete(route("wishlist.destroy", product.id), {
            onFinish: () => setIsLoading(false),
        });
    };

    const toggleWishlist = () => {
        if (product.isInWishlist) {
            handleRemoveFromWishlist();
        } else {
            handleAddToWishlist();
        }
    }

    return (
        <Button
            type="button"
            variant="outline"
            size="icon"
            onClick={toggleWishlist}
            disabled={isLoading}
            aria-label={
                product.isInWishlist ? "إزالة من المفضلة" : "إضافة إلى المفضلة"
            }
        >
            <Heart
                size={20}
                className={`${
                    product.isInWishlist ? "text-red-500" : "text-secondary-600"
                }`}
                fill={
                    product.isInWishlist || isLoading ? "currentColor" : "none"
                }
            />
        </Button>
    );
}
