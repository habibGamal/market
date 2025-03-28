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
}

export function ProductCard({ product }: ProductCardProps) {
    const [open, setOpen] = useState(false);
    const { packets, setPackets, pieces, setPieces, loading, addToCart } = useCart({
        onSuccess: () => setOpen(false),
    });

    // Determine if product is out of stock
    const isOutOfStock = product.has_stock === false;

    return (
        <div className={`relative group flex flex-col justify-between h-full max-w-[250px] ${isOutOfStock ? 'opacity-60' : ''}`}>
            {/* Badge */}
            {(product.is_new || product.is_deal || isOutOfStock) && (
                <Badge
                    className="absolute top-2 right-2 z-10"
                    variant={isOutOfStock ? "outline" : product.is_deal ? "destructive" : "default"}
                >
                    {isOutOfStock ? "غير متوفر" : product.is_deal ? "عرض خاص" : "جديد"}
                </Badge>
            )}
            {/* Product Image */}
            <Link href={route('products.show', product.id)} preserveState preserveScroll className="block">
                <div className={`relative aspect-square overflow-hidden rounded-lg mb-3 ${isOutOfStock ? 'grayscale' : ''}`}>
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
                            <span className="text-xs">ج.م/باكيت</span>
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
                            <span className="text-xs">ج.م/قطعة</span>
                        </span>
                    </div>
                )}
                {/* Add to Cart Dialog */}
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogTrigger asChild>
                        <Button
                            className="w-full mt-2"
                            disabled={isOutOfStock}
                            variant={isOutOfStock ? "outline" : "default"}
                        >
                            {isOutOfStock ? "غير متوفر" : "أضف للسلة"}
                        </Button>
                    </DialogTrigger>
                </Dialog>
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
