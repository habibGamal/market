import { useState } from "react";
import { WishlistButton } from "@/Components/Products/WishlistButton";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Dialog, DialogTrigger } from "@/Components/ui/dialog";
import { useCart } from "@/Hooks/useCart";
import { AddToCartModal } from "@/Components/Products/AddToCartModal";
import type { Product } from "@/types";
import { Link } from "@inertiajs/react";

interface ShowProps {
    product: Product & {
        category: {
            id: number;
            name: string;
        };
        brand: {
            id: number;
            name: string;
        };
        barcode: string;
        packet_to_piece: number;
        description?: string;
    };
}

export default function Show({ product }: ShowProps) {
    const [open, setOpen] = useState(false);
    const { packets, setPackets, pieces, setPieces, loading, addToCart } =
        useCart({
            onSuccess: () => setOpen(false),
        });

    // Determine if product is out of stock
    const isDisabled = product.has_stock === false || !product.is_active;

    return (
        <div className="space-y-12">
            <div className="container mx-auto px-4 py-8">
                <div className="grid md:grid-cols-2 gap-8">
                    {/* Product Image */}
                    <div className="relative">
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
                        <FallbackImage
                            src={product.image}
                            alt={product.name}
                            className={`w-full rounded-lg aspect-square object-cover ${
                                isDisabled ? "grayscale" : ""
                            }`}
                        />
                    </div>

                    {/* Product Info */}
                    <div
                        className={`space-y-6 ${
                            isDisabled ? "opacity-60" : ""
                        }`}
                    >
                        <h1 className="text-3xl font-bold text-secondary-900">
                            {product.name}
                        </h1>

                        {/* Product Description */}
                        {product.description && (
                            <div className="text-secondary-700 text-sm border-r-2 border-primary-500 pr-4 py-1">
                                {product.description}
                            </div>
                        )}

                        <div className="space-y-2">
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">الفئة:</span>{" "}
                                <Link
                                    href={route('categories.show', product.category.id)}
                                    className="text-primary-600 hover:underline"
                                >
                                    {product.category.name}
                                </Link>
                            </div>
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">
                                    العلامة التجارية:
                                </span>{" "}
                                <Link
                                    href={`/product-list?id=${product.brand.id}&model=brand`}
                                    className="text-primary-600 hover:underline"
                                >
                                    {product.brand.name}
                                </Link>
                            </div>
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">
                                    عدد القطع في العبوة:
                                </span>{" "}
                                {product.packet_to_piece}
                            </div>
                        </div>

                        <div className="space-y-4">
                            {/* Packet Price */}
                            {product.prices.packet.discounted && (
                                <div className="flex items-baseline gap-2">
                                    {product.prices.packet.original && (
                                        <span className="text-lg text-secondary-500 line-through">
                                            {product.prices.packet.original}
                                        </span>
                                    )}
                                    <span className="text-2xl font-bold text-primary-500">
                                        {product.prices.packet.discounted}{" "}
                                        <span className="text-sm">
                                            ج.م/{product.packet_alter_name}
                                        </span>
                                    </span>
                                </div>
                            )}

                            {/* Piece Price */}
                            {product.prices.piece.discounted && (
                                <div className="flex items-baseline gap-2">
                                    {product.prices.piece.original && (
                                        <span className="text-sm text-secondary-500 line-through">
                                            {product.prices.piece.original}
                                        </span>
                                    )}
                                    <span className="text-xl text-secondary-700">
                                        {product.prices.piece.discounted}{" "}
                                        <span className="text-sm">
                                            ج.م/{product.piece_alter_name}
                                        </span>
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Add to Cart and Wishlist */}
                        <div className="flex gap-2">
                            <Dialog open={open} onOpenChange={setOpen}>
                                <DialogTrigger asChild>
                                    <Button
                                        size="lg"
                                        className="w-full"
                                        disabled={isDisabled}
                                        variant={isDisabled ? "outline" : "default"}
                                    >
                                        {isDisabled ? "غير متوفر" : "أضف للسلة"}
                                    </Button>
                                </DialogTrigger>
                            </Dialog>

                            {/* Wishlist Button */}
                            <WishlistButton product={product} />
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
            </div>

            {/* Related Products Section */}
            {/* <div className="container mx-auto px-4">
                <ProductsHorizontalSection
                    title="منتجات مشابهة"
                    limit={20}
                    selectedCategories={[product.category.id]}
                    selectedBrands={[product.brand.id]}
                    showFilters={false}
                />
            </div> */}
        </div>
    );
}
