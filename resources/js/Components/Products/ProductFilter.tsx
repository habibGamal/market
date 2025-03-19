import { useState, useMemo } from "react";
import { Button } from "@/Components/ui/button";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from "@/Components/ui/dialog";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { FilterOption, ProductFilters } from "@/types";
import { FilterButton } from "./Filter/FilterButton";
import { SortingSelect } from "./Filter/SortingSelect";
import { PriceRangeFilter } from "./Filter/PriceRangeFilter";
import { CategoriesFilter } from "./Filter/CategoriesFilter";
import { BrandsFilter } from "./Filter/BrandsFilter";

interface ProductFilterProps {
    categories: FilterOption[];
    brands: FilterOption[];
    selectedCategories?: number[];
    selectedBrands?: number[];
    initialMinPrice?: string;
    initialMaxPrice?: string;
    onFilter: (filters: ProductFilters) => void;
}

export function ProductFilter({
    categories,
    brands,
    selectedCategories = [],
    selectedBrands = [],
    initialMinPrice = "",
    initialMaxPrice = "",
    onFilter,
}: ProductFilterProps) {
    const [localSelectedCategories, setLocalSelectedCategories] =
        useState<number[]>(selectedCategories);
    const [localSelectedBrands, setLocalSelectedBrands] =
        useState<number[]>(selectedBrands);
    const [minPrice, setMinPrice] = useState<string>(initialMinPrice);
    const [maxPrice, setMaxPrice] = useState<string>(initialMaxPrice);
    const [isOpen, setIsOpen] = useState(false);

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

    const handleToggleCategory = (categoryId: number, isChecked: boolean) => {
        setLocalSelectedCategories((prev) =>
            isChecked
                ? [...prev, categoryId]
                : prev.filter((id) => id !== categoryId)
        );
    };

    return (
        <div className="flex items-center gap-4 mb-6">
            <Dialog open={isOpen} onOpenChange={setIsOpen}>
                <FilterButton activeFiltersCount={activeFiltersCount} />
                <DialogContent className="max-w-sm" dir="rtl">
                    <DialogHeader>
                        <DialogTitle>تصفية المنتجات</DialogTitle>
                    </DialogHeader>
                    <ScrollArea className="max-h-[60vh]" dir="rtl">
                        <div className="space-y-6 p-4">
                            <PriceRangeFilter
                                minPrice={minPrice}
                                maxPrice={maxPrice}
                                setMinPrice={setMinPrice}
                                setMaxPrice={setMaxPrice}
                            />

                            {categories.length > 0 && (
                                <CategoriesFilter
                                    categories={categories}
                                    selectedCategories={localSelectedCategories}
                                    onToggleCategory={handleToggleCategory}
                                />
                            )}

                            <BrandsFilter
                                brands={brands}
                                selectedBrands={localSelectedBrands}
                                setSelectedBrands={setLocalSelectedBrands}
                            />
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

            <SortingSelect onSort={handleSort} />
        </div>
    );
}
