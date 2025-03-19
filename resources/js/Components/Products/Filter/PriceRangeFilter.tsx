import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { cn } from "@/lib/utils";

interface PriceRangeProps {
    minPrice: string;
    maxPrice: string;
    setMinPrice: (value: string) => void;
    setMaxPrice: (value: string) => void;
}

export const PriceRangeFilter = ({ minPrice, maxPrice, setMinPrice, setMaxPrice }: PriceRangeProps) => {
    return (
        <div className="space-y-4">
            <Label>نطاق السعر</Label>
            <div className="grid grid-cols-2 gap-3">
                <div className="space-y-2">
                    <Label htmlFor="min-price" className="text-sm font-normal">
                        السعر الأدنى
                    </Label>
                    <Input
                        id="min-price"
                        type="number"
                        min="0"
                        value={minPrice}
                        onChange={(e) => setMinPrice(e.target.value)}
                        placeholder="0"
                        className={cn(
                            "text-left",
                            "[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        )}
                    />
                </div>
                <div className="space-y-2">
                    <Label htmlFor="max-price" className="text-sm font-normal">
                        السعر الأقصى
                    </Label>
                    <Input
                        id="max-price"
                        type="number"
                        min="0"
                        value={maxPrice}
                        onChange={(e) => setMaxPrice(e.target.value)}
                        placeholder="∞"
                        className={cn(
                            "text-left",
                            "[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                        )}
                    />
                </div>
            </div>
        </div>
    );
};
