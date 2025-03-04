import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Minus, Plus, Trash2 } from "lucide-react";
import { Product } from "@/types";

interface CartItemProps {
    product: Product;
    packets: number;
    pieces: number;
    onUpdateQuantity: (packets: number, pieces: number) => void;
    onRemove: () => void;
}

export function CartItem({ product, packets, pieces, onUpdateQuantity, onRemove }: CartItemProps) {
    const handlePacketsChange = (value: number) => {
        onUpdateQuantity(Math.max(0, value), pieces);
    };

    const handlePiecesChange = (value: number) => {
        onUpdateQuantity(packets, Math.max(0, value));
    };

    return (
        <div className="flex gap-4 p-4 border-b last:border-0">
            {/* Product Image */}
            <div className="w-16 h-16 shrink-0">
                <FallbackImage
                    src={product.image}
                    alt={product.name}
                    className="w-full h-full object-cover rounded-md"
                />
            </div>

            {/* Product Details */}
            <div className="flex-1 min-w-0">
                <div className="flex justify-between items-start gap-2">
                    <div>
                        <h3 className="font-medium text-secondary-900 truncate">{product.name}</h3>
                        <div className="mt-1 text-sm text-secondary-500">
                            {product.prices.packet.discounted} ج.م/باكيت · {product.prices.piece.discounted} ج.م/قطعة
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="text-secondary-500 hover:text-destructive"
                        onClick={onRemove}
                    >
                        <Trash2 className="h-5 w-5" />
                    </Button>
                </div>

                {/* Quantity Controls */}
                <div className="mt-4 flex flex-wrap gap-4">
                    <div className="flex items-center gap-2">
                        <span className="text-sm text-secondary-600">باكيت:</span>
                        <div className="flex items-center">
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-l-none"
                                onClick={() => handlePacketsChange(packets - 1)}
                            >
                                <Minus className="h-4 w-4" />
                            </Button>
                            <Input
                                type="number"
                                min={0}
                                value={packets}
                                onChange={(e) => handlePacketsChange(parseInt(e.target.value) || 0)}
                                className="h-8 w-16 rounded-none text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-r-none"
                                onClick={() => handlePacketsChange(packets + 1)}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="text-sm text-secondary-600">قطع:</span>
                        <div className="flex items-center">
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-l-none"
                                onClick={() => handlePiecesChange(pieces - 1)}
                            >
                                <Minus className="h-4 w-4" />
                            </Button>
                            <Input
                                type="number"
                                min={0}
                                value={pieces}
                                onChange={(e) => handlePiecesChange(parseInt(e.target.value) || 0)}
                                className="h-8 w-16 rounded-none text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                            />
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-r-none"
                                onClick={() => handlePiecesChange(pieces + 1)}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>

                {/* Item Total */}
                <div className="mt-2 text-sm font-medium text-secondary-900">
                    الإجمالي: {((packets * parseFloat(product.prices.packet.discounted)) + (pieces * parseFloat(product.prices.piece.discounted))).toFixed(2)} ج.م
                </div>
            </div>
        </div>
    );
}
