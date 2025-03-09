import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Minus, Plus, Trash2, Loader2 } from "lucide-react";
import { Product } from "@/types";
import { useCallback, useState } from "react";
import debounce from "lodash/debounce";

interface CartItemProps {
    id: number;
    product: Product;
    packets: number;
    pieces: number;
    total: number;
    errorMsg?: string;
    onUpdateQuantity: (packets: number, pieces: number) => void;
    onRemove: () => void;
    loading?: boolean;
}

export function CartItem({
    id,
    product,
    packets,
    pieces,
    total,
    errorMsg,
    onUpdateQuantity,
    onRemove,
    loading = false,
}: CartItemProps) {
    const debouncedUpdateQuantity = useCallback(
        debounce((newPackets: number, newPieces: number) => {
            onUpdateQuantity(newPackets, newPieces);
        }, 500),
        [onUpdateQuantity]
    );

    const [packetsQuantity, setPacketsQuantity] = useState(packets);
    const [piecesQuantity, setPiecesQuantity] = useState(pieces);

    const handlePacketsChange = (value: number) => {
        const newValue = Math.max(0, value);
        setPacketsQuantity(newValue);
        debouncedUpdateQuantity(newValue, pieces);
    };

    const handlePiecesChange = (value: number) => {
        const newValue = Math.max(0, value);
        setPiecesQuantity(newValue);
        debouncedUpdateQuantity(packets, newValue);
    };

    return (
        <div className="flex flex-col  gap-4 p-4 border-b last:border-0 relative">
            {loading && (
                <div className="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-50">
                    <Loader2 className="h-6 w-6 animate-spin text-primary" />
                </div>
            )}
            <div className="flex gap-4">
                {/* Product Image */}
                <div className="w-12 h-12 shrink-0">
                    <FallbackImage
                        src={product.image}
                        alt={product.name}
                        className="rounded-lg"
                    />
                </div>

                <div className="flex flex-1 justify-between items-start gap-2">
                    <div>
                        <h3 className="font-medium text-secondary-900 text-lg mb-1">
                            {product.name}
                        </h3>
                        <div className="text-sm text-secondary-500 space-y-1">
                            <div>
                                سعر العبوة: {product.prices.packet.discounted}{" "}
                                ج.م
                                {product.prices.packet.original && (
                                    <span className="text-xs line-through mr-1 text-secondary-400">
                                        {product.prices.packet.original} ج.م
                                    </span>
                                )}
                            </div>
                            <div>
                                سعر القطعة: {product.prices.piece.discounted}{" "}
                                ج.م
                                {product.prices.piece.original && (
                                    <span className="text-xs line-through mr-1 text-secondary-400">
                                        {product.prices.piece.original} ج.م
                                    </span>
                                )}
                            </div>
                        </div>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="text-secondary-500 hover:text-destructive"
                        onClick={onRemove}
                        disabled={loading}
                    >
                        <Trash2 className="h-5 w-5" />
                    </Button>
                </div>
            </div>

            {/* Product Details */}
            <div className="flex-1 min-w-0 space-y-4">
                {/* Quantity Controls */}
                <div className="flex flex-wrap gap-4">
                    <div className="flex items-center gap-2">
                        <span className="text-sm text-secondary-600 min-w-16">
                            باكيت:
                        </span>
                        <div className="flex items-center">
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-l-none"
                                onClick={() =>
                                    handlePacketsChange(packetsQuantity - 1)
                                }
                                disabled={loading || packets <= 0}
                            >
                                <Minus className="h-4 w-4" />
                            </Button>
                            <Input
                                type="number"
                                min={0}
                                value={packetsQuantity}
                                onChange={(e) =>
                                    handlePacketsChange(
                                        parseInt(e.target.value) || 0
                                    )
                                }
                                className="h-8 w-16 rounded-none text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                disabled={loading}
                            />
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-r-none"
                                onClick={() =>
                                    handlePacketsChange(packetsQuantity + 1)
                                }
                                disabled={loading}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <span className="text-sm text-secondary-600 min-w-16">
                            قطع:
                        </span>
                        <div className="flex items-center">
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-l-none"
                                onClick={() =>
                                    handlePiecesChange(piecesQuantity - 1)
                                }
                                disabled={loading || pieces <= 0}
                            >
                                <Minus className="h-4 w-4" />
                            </Button>
                            <Input
                                type="number"
                                min={0}
                                max={product.packet_to_piece}
                                value={piecesQuantity}
                                onChange={(e) =>
                                    handlePiecesChange(
                                        parseInt(e.target.value) || 0
                                    )
                                }
                                className="h-8 w-16 rounded-none text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                disabled={loading}
                            />
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 rounded-r-none"
                                onClick={() =>
                                    handlePiecesChange(piecesQuantity + 1)
                                }
                                disabled={loading}
                            >
                                <Plus className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>

                    {errorMsg && (
                        <div className="text-xs text-destructive">
                            {errorMsg}
                        </div>
                    )}
                </div>

                {/* Item Total */}
                <div className="text-base font-medium text-secondary-900">
                    الإجمالي: {total.toFixed(2)} ج.م
                </div>
            </div>
        </div>
    );
}
