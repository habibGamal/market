import { Button } from "@/Components/ui/button";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { QuantityInput } from "@/Components/ui/quantity-input";
import { Trash2, Loader2 } from "lucide-react";
import { Product } from "@/types";
import { useCallback, useEffect, useState } from "react";
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

    const enablePieces = product.packet_to_piece > 1 && product.can_sell_pieces;

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
                                سعر {product.packet_alter_name}: {product.prices.packet.discounted}{" "}
                                ج.م
                                {product.prices.packet.original && (
                                    <span className="text-xs line-through mr-1 text-secondary-400">
                                        {product.prices.packet.original} ج.م
                                    </span>
                                )}
                            </div>
                            {product.packet_to_piece > 1 && (
                                <div>
                                    سعر {product.piece_alter_name}:{" "}
                                    {product.prices.piece.discounted} ج.م
                                    {product.prices.piece.original && (
                                        <span className="text-xs line-through mr-1 text-secondary-400">
                                            {product.prices.piece.original} ج.م
                                        </span>
                                    )}
                                </div>
                            )}
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
                            {product.packet_alter_name}:
                        </span>
                        <QuantityInput
                            value={packetsQuantity}
                            onChange={handlePacketsChange}
                            min={0}
                            disabled={loading}
                        />
                    </div>
                    {enablePieces && (
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-secondary-600 min-w-16">
                                {product.piece_alter_name}:
                            </span>
                            <QuantityInput
                                value={piecesQuantity}
                                onChange={handlePiecesChange}
                                min={0}
                                max={product.packet_to_piece}
                                disabled={loading}
                            />
                        </div>
                    )}

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
