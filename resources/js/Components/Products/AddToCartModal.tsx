import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/Components/ui/dialog";
import { QuantityInput } from "@/Components/ui/quantity-input";
import { Product } from "@/types";

interface AddToCartModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    product: Product;
    packets: number;
    setPackets: (value: number) => void;
    pieces: number;
    setPieces: (value: number) => void;
    loading: boolean;
    onAddToCart: () => void;
}

export function AddToCartModal({
    open,
    onOpenChange,
    product,
    packets,
    setPackets,
    pieces,
    setPieces,
    loading,
    onAddToCart,
}: AddToCartModalProps) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>تحديد الكمية</DialogTitle>
                </DialogHeader>
                <div className="space-y-4 py-4">
                    <div className="flex gap-2 items-center">
                        <label className="text-sm font-medium min-w-24">
                            عدد الباكيتات
                        </label>
                        <QuantityInput
                            value={packets}
                            onChange={setPackets}
                            min={0}
                            disabled={loading}
                        />
                    </div>
                    <div className="flex gap-2 items-center">
                        <label className="text-sm font-medium min-w-24">
                            عدد القطع
                        </label>
                        <QuantityInput
                            value={pieces}
                            onChange={setPieces}
                            min={0}
                            max={product.packet_to_piece}
                            disabled={loading}
                        />
                    </div>
                    <Button
                        className="w-full"
                        onClick={onAddToCart}
                        disabled={loading || (packets === 0 && pieces === 0)}
                    >
                        {loading ? "جاري الإضافة..." : "إضافة"}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
