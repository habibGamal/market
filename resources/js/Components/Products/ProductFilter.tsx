import { useState, useMemo } from "react";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Filter } from "lucide-react";
import { Checkbox } from "@/Components/ui/checkbox";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { Input } from "@/Components/ui/input";
import { cn } from "@/lib/utils";

interface FilterOption {
    id: number;
    name: string;
}

interface ProductFilterProps {
    categories: FilterOption[];
    brands: FilterOption[];
    selectedCategories?: number[];
    selectedBrands?: number[];
    initialMinPrice?: string;
    initialMaxPrice?: string;
    onFilter: (filters: {
        categories?: number[];
        brands?: number[];
        sortBy?: string;
        sortDirection?: "asc" | "desc";
        minPrice?: number;
        maxPrice?: number;
    }) => void;
}

const sortOptions = [
    { label: "الأحدث", value: "created_at:desc" },
    { label: "الأقدم", value: "created_at:asc" },
    { label: "السعر: الأعلى للأقل", value: "packet_price:desc" },
    { label: "السعر: الأقل للأعلى", value: "packet_price:asc" },
    { label: "الاسم: أ-ي", value: "name:asc" },
    { label: "الاسم: ي-أ", value: "name:desc" },
];

export function ProductFilter({
    categories,
    brands,
    selectedCategories = [],
    selectedBrands = [],
    initialMinPrice = "",
    initialMaxPrice = "",
    onFilter,
}: ProductFilterProps) {
    const [localSelectedCategories, setLocalSelectedCategories] = useState<number[]>(selectedCategories);
    const [localSelectedBrands, setLocalSelectedBrands] = useState<number[]>(selectedBrands);
    const [minPrice, setMinPrice] = useState<string>(initialMinPrice);
    const [maxPrice, setMaxPrice] = useState<string>(initialMaxPrice);
    const [isOpen, setIsOpen] = useState(false);

    // Calculate number of active filters
    const activeFiltersCount = useMemo(() => {
        let count = 0;
        if (localSelectedCategories.length > 0) count++;
        if (localSelectedBrands.length > 0) count++;
        if (minPrice || maxPrice) count++;
        return count;
    }, [localSelectedCategories, localSelectedBrands, minPrice, maxPrice]);

    const handleSort = (value: string) => {
        const [sortBy, sortDirection] = value.split(":");
        onFilter({ sortBy, sortDirection: sortDirection as "asc" | "desc" });
    };

    const handleFilter = () => {
        onFilter({
            categories: localSelectedCategories,
            brands: localSelectedBrands,
            minPrice: minPrice ? Number(minPrice) : undefined,
            maxPrice: maxPrice ? Number(maxPrice) : undefined,
        });
        setIsOpen(false);
    };

    const handleReset = () => {
        setLocalSelectedCategories([]);
        setLocalSelectedBrands([]);
        setMinPrice("");
        setMaxPrice("");
    };

    return (
        <div className="flex items-center gap-4 mb-6">
            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                <DialogTrigger asChild>
                    <Button variant="outline"  className="relative">
                        <Filter className="h-4 w-4 ml-2" />
                        تصفية
                        {activeFiltersCount > 0 && (
                            <span className="absolute -top-2 -right-2 min-w-[18px] h-[18px] rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center">
                                {activeFiltersCount}
                            </span>
                        )}
                    </Button>
                </DialogTrigger>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>تصفية المنتجات</DialogTitle>
                    </DialogHeader>
                    <ScrollArea className="max-h-[60vh]">
                        <div className="space-y-6 p-4">
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

                            {categories.length > 0 && (
                                <div className="space-y-4">
                                    <Label>الفئات</Label>
                                    <div className="grid grid-cols-1 gap-3">
                                        {categories.map((category) => (
                                            <div key={category.id} className="flex items-center gap-2">
                                                <Checkbox
                                                    id={`category-${category.id}`}
                                                    checked={localSelectedCategories.includes(category.id)}
                                                    onCheckedChange={(checked) => {
                                                        setLocalSelectedCategories((prev) =>
                                                            checked
                                                                ? [...prev, category.id]
                                                                : prev.filter((id) => id !== category.id)
                                                        );
                                                    }}
                                                />
                                                <Label htmlFor={`category-${category.id}`} className="text-sm font-normal cursor-pointer">
                                                    {category.name}
                                                </Label>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {brands.length > 0 && (
                                <div className="space-y-4">
                                    <Label>العلامات التجارية</Label>
                                    <div className="grid grid-cols-1 gap-3">
                                        {brands.map((brand) => (
                                            <div key={brand.id} className="flex items-center gap-2">
                                                <Checkbox
                                                    id={`brand-${brand.id}`}
                                                    checked={localSelectedBrands.includes(brand.id)}
                                                    onCheckedChange={(checked) => {
                                                        setLocalSelectedBrands((prev) =>
                                                            checked
                                                                ? [...prev, brand.id]
                                                                : prev.filter((id) => id !== brand.id)
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
                            )}
                        </div>
                    </ScrollArea>
                    <div className="flex justify-end gap-2 pt-4">
                        <Button variant="outline" onClick={handleReset}>
                            إعادة تعيين
                        </Button>
                        <Button onClick={handleFilter}>تطبيق</Button>
                    </div>
                </DialogContent>
            </Dialog>

            <Select onValueChange={handleSort}>
                <SelectTrigger className="w-[180px]" dir="rtl">
                    <SelectValue placeholder="ترتيب حسب" />
                </SelectTrigger>
                <SelectContent>
                    {sortOptions.map((option) => (
                        <SelectItem key={option.value} value={option.value}>
                            {option.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
        </div>
    );
}
