import { useState } from "react";
import { Link } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogTrigger } from "@/Components/ui/dialog";
import { Badge } from "@/Components/ui/badge";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Product } from "@/types";
import { useCart } from "@/Hooks/useCart";
import { AddToCartModal } from "@/Components/Products/AddToCartModal";

interface ProductCardProps {
    product: Product;
    additionalActions?: React.ReactNode;
}

export function ProductCard({ product, additionalActions }: ProductCardProps) {
    const [open, setOpen] = useState(false);
    const { packets, setPackets, pieces, setPieces, loading, addToCart } =
        useCart({
            onSuccess: () => setOpen(false),
        });

    // Determine if product is out of stock
    const isDisabled = product.has_stock === false || !product.is_active;

    return (
        <div
            className={`relative group flex flex-col justify-between h-full max-w-[250px] ${
                isDisabled ? "opacity-60" : ""
            }`}
        >
            {/* Badge */}
            {(product.is_new || product.is_deal || isDisabled) && (
                <Badge
                    className="absolute top-2 right-2 z-10"
                    variant={
                        isDisabled
                            ? "outline"
                            : product.is_deal
                            ? "destructive"
                            : "default"
                    }
                >
                    {isDisabled
                        ? "غير متوفر"
                        : product.is_deal
                        ? "عرض خاص"
                        : "جديد"}
                </Badge>
            )}
            {/* Product Image */}
            <Link
                href={route("products.show", product.id)}
                preserveState
                preserveScroll
                className="block"
            >
                <div
                    className={`relative aspect-square overflow-hidden rounded-lg mb-3 ${
                        isDisabled ? "grayscale" : ""
                    }`}
                >
                    <FallbackImage
                        src={product.image}
                        alt={product.name}
                        className="transition-transform group-hover:scale-105"
                    />
                </div>
                {/* Product Name */}
                <h3 className="font-medium text-secondary-900 line-clamp-2 hover:text-primary-500 transition-colors">
                    {product.name}
                </h3>
            </Link>
            {/* Product Info */}
            <div className="space-y-1 mt-2">
                {/* Packet Price */}
                {product.prices.packet.discounted && (
                    <div className="flex items-baseline gap-2">
                        {product.prices.packet.original && (
                            <span className="text-sm text-secondary-500 line-through">
                                {product.prices.packet.original}
                            </span>
                        )}
                        <span className="text-lg font-bold text-primary-500">
                            {product.prices.packet.discounted}{" "}
                            <span className="text-xs">
                                ج.م/{product.packet_alter_name}
                            </span>
                        </span>
                    </div>
                )}
                {/* Piece Price */}
                {product.prices.piece.discounted && (
                    <div className="flex items-baseline gap-2">
                        {product.prices.piece.original && (
                            <span className="text-xs text-secondary-500 line-through">
                                {product.prices.piece.original}
                            </span>
                        )}
                        <span className="text-sm text-secondary-700">
                            {product.prices.piece.discounted}{" "}
                            <span className="text-xs">
                                ج.م/{product.piece_alter_name}
                            </span>
                        </span>
                    </div>
                )}
                {/* Add to Cart Dialog */}
                <div className="flex gap-2 items-center mt-4">
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button
                                className="flex-1"
                                disabled={isDisabled}
                                variant={isDisabled ? "outline" : "default"}
                            >
                                {isDisabled ? "غير متوفر" : "أضف للسلة"}
                            </Button>
                        </DialogTrigger>
                    </Dialog>
                    {additionalActions}
                </div>
                <AddToCartModal
                    open={open}
                    onOpenChange={setOpen}
                    product={product}
                    packets={packets}
                    setPackets={setPackets}
                    pieces={pieces}
                    setPieces={setPieces}
                    loading={loading}
                    onAddToCart={() => addToCart(product.id)}
                />
            </div>
        </div>
    );
}
