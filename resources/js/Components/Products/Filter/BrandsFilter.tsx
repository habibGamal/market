import { Label } from "@/Components/ui/label";
import { Checkbox } from "@/Components/ui/checkbox";
import { FilterOption } from "@/types";

interface BrandsFilterProps {
    brands: FilterOption[];
    selectedBrands: number[];
    setSelectedBrands: (brands: number[]) => void;
}

export const BrandsFilter = ({ brands, selectedBrands, setSelectedBrands }: BrandsFilterProps) => {
    if (brands.length === 0) {
        return null;
    }

    return (
        <div className="space-y-4">
            <Label>العلامات التجارية</Label>
            <div className="grid grid-cols-1 gap-3">
                {brands.map((brand) => (
                    <div key={brand.id} className="flex items-center gap-2">
                        <Checkbox
                            id={`brand-${brand.id}`}
                            checked={selectedBrands.includes(brand.id)}
                            onCheckedChange={(checked) => {
                                setSelectedBrands(
                                    checked
                                        ? [...selectedBrands, brand.id]
                                        : selectedBrands.filter((id) => id !== brand.id)
                                );
                            }}
                        />
                        <Label htmlFor={`brand-${brand.id}`} className="text-sm font-normal cursor-pointer">
                            {brand.name}
                        </Label>
                    </div>
                ))}
            </div>
        </div>
    );
};
