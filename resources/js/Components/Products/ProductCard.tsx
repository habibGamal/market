import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Badge } from "@/Components/ui/badge";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Product } from "@/types";

interface ProductCardProps {
    product: Product;
}

export function ProductCard({ product }: ProductCardProps) {
    const [packets, setPackets] = useState(0);
    const [pieces, setPieces] = useState(0);

    const handleAddToCart = () => {
        setPackets(0);
        setPieces(0);
    };

    return (
        <div className="relative group">
            {/* Badge */}
            {(product.isNew || product.isDeal) && (
                <Badge
                    className="absolute top-2 right-2 z-10"
                    variant={product.isDeal ? "destructive" : "default"}
                >
                    {product.isDeal ? "عرض خاص" : "جديد"}
                </Badge>
            )}
            {/* Product Image */}
            <Link href={route('products.show', product.id)} preserveState preserveScroll  className="block">
                <div className="relative aspect-square overflow-hidden rounded-lg mb-3">
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
                <Dialog>
                    <DialogTrigger asChild>
                        <Button className="w-full mt-2">أضف للسلة</Button>
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
                                />
                            </div>
                            <Button
                                className="w-full"
                                onClick={handleAddToCart}
                                disabled={packets === 0 && pieces === 0}
                            >
                                إضافة
                            </Button>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
