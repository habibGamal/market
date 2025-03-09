import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { ProductsHorizontalSection } from "@/Components/Products/ProductsSection";
import { useCart } from "@/hooks/useCart";
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

    return (
        <div className="space-y-12">
            <div className="container mx-auto px-4 py-8">
                <div className="grid md:grid-cols-2 gap-8">
                    {/* Product Image */}
                    <div className="relative">
                        {/* Badge */}
                        {(product.is_new || product.is_deal) && (
                            <Badge
                                className="absolute top-2 right-2 z-10"
                                variant={product.is_deal ? "destructive" : "default"}
                            >
                                {product.is_deal ? "عرض خاص" : "جديد"}
                            </Badge>
                        )}
                        <FallbackImage
                            src={product.image}
                            alt={product.name}
                            className="w-full rounded-lg aspect-square object-cover"
                        />
                    </div>

                    {/* Product Info */}
                    <div className="space-y-6">
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

                        {/* Add to Cart Dialog */}
                        <Dialog open={open} onOpenChange={setOpen}>
                            <DialogTrigger asChild>
                                <Button size="lg" className="w-full">
                                    أضف للسلة
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>تحديد الكمية</DialogTitle>
                                </DialogHeader>
                                <div className="space-y-4 py-4">
                                    <div className="space-y-2">
                                        <label className="text-sm font-medium">
                                            عدد الباكيتات
                                        </label>
                                        <Input
                                            type="number"
                                            min="0"
                                            value={packets}
                                            onChange={(e) =>
                                                setPackets(parseInt(e.target.value) || 0)
                                            }
                                            disabled={loading}
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-sm font-medium">
                                            عدد القطع
                                        </label>
                                        <Input
                                            type="number"
                                            min="0"
                                            value={pieces}
                                            onChange={(e) =>
                                                setPieces(parseInt(e.target.value) || 0)
                                            }
                                            disabled={loading}
                                        />
                                    </div>
                                    <Button
                                        className="w-full"
                                        onClick={() => addToCart(product.id)}
                                        disabled={loading || (packets === 0 && pieces === 0)}
                                    >
                                        {loading ? "جاري الإضافة..." : "إضافة"}
                                    </Button>
                                </div>
                            </DialogContent>
                        </Dialog>
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
