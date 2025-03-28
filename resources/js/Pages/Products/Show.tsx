import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Dialog, DialogTrigger } from "@/Components/ui/dialog";
import { ProductsHorizontalSection } from "@/Components/Products/ProductsSection";
import { useCart } from "@/Hooks/useCart";
import { AddToCartModal } from "@/Components/Products/AddToCartModal";
import type { Product } from "@/types";

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
    };
}

export default function Show({ product }: ShowProps) {
    const [open, setOpen] = useState(false);
    const { packets, setPackets, pieces, setPieces, loading, addToCart } = useCart({
        onSuccess: () => setOpen(false),
    });

    // Determine if product is out of stock
    const isOutOfStock = product.has_stock === false;

    return (
        <div className="space-y-12">
            <div className="container mx-auto px-4 py-8">
                <div className="grid md:grid-cols-2 gap-8">
                    {/* Product Image */}
                    <div className="relative">
                        {/* Badge */}
                        {(product.is_new || product.is_deal || isOutOfStock) && (
                            <Badge
                                className="absolute top-2 right-2 z-10"
                                variant={isOutOfStock ? "outline" : product.is_deal ? "destructive" : "default"}
                            >
                                {isOutOfStock ? "غير متوفر" : product.is_deal ? "عرض خاص" : "جديد"}
                            </Badge>
                        )}
                        <FallbackImage
                            src={product.image}
                            alt={product.name}
                            className={`w-full rounded-lg aspect-square object-cover ${isOutOfStock ? 'grayscale' : ''}`}
                        />
                    </div>

                    {/* Product Info */}
                    <div className={`space-y-6 ${isOutOfStock ? 'opacity-60' : ''}`}>
                        <h1 className="text-3xl font-bold text-secondary-900">{product.name}</h1>

                        <div className="space-y-2">
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">الباركود:</span> {product.barcode}
                            </div>
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">الفئة:</span> {product.category.name}
                            </div>
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">العلامة التجارية:</span> {product.brand.name}
                            </div>
                            <div className="text-sm text-secondary-600">
                                <span className="font-medium">عدد القطع في العبوة:</span> {product.packet_to_piece}
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
                                        <span className="text-sm">ج.م/باكيت</span>
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
                                        <span className="text-sm">ج.م/قطعة</span>
                                    </span>
                                </div>
                            )}
                        </div>

                        {/* Add to Cart */}
                        <Dialog open={open} onOpenChange={setOpen}>
                            <DialogTrigger asChild>
                                <Button
                                    size="lg"
                                    className="w-full"
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
