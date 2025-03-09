import { Brand, Category } from "@/types";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/Components/ui/sheet";
import { Button } from "../ui/button";
import { cn } from "@/lib/utils";
import { useCallback, useState } from "react";
import { Filter, SlidersHorizontal } from "lucide-react";
import { Checkbox } from "@/Components/ui/checkbox";
import { ScrollArea } from "@/Components/ui/scroll-area";
import { Label } from "@/Components/ui/label";
import { useBreakpoint } from "@/Hooks/useBreakpoint";

interface ProductFiltersProps {
    categories: Category[];
    selectedCategories: number[];
    brands: Brand[];
    selectedBrands: number[];
    onFilter: (filters: {
        categories?: number[];
        brands?: number[];
        sortBy?: string;
        sortDirection?: "asc" | "desc";
    }) => void;
}

const sortOptions = [
    { label: "الأحدث", value: "created_at:desc" },
    { label: "الأقدم", value: "created_at:asc" },
    { label: "السعر: الأعلى للأقل", value: "packet_price:desc" },
    { label: "السعر: الأقل للأعلى", value: "packet_price:asc" },
    { label: "الاسم: أ-ي", value: "name:asc" },
    { label: "الاسم: ي-أ", value: "name:desc" },
] as const;

export function ProductFilters({
    categories,
    selectedCategories = [],
    brands,
    selectedBrands = [],
    onFilter,
}: ProductFiltersProps) {
    const [isOpen, setIsOpen] = useState(false);
    const [localSelectedCategories, setLocalSelectedCategories] = useState<number[]>(selectedCategories);
    const [localSelectedBrands, setLocalSelectedBrands] = useState<number[]>(selectedBrands);
    const { isLg } = useBreakpoint("lg");

    const handleSort = (value: string) => {
        const [sortBy, sortDirection] = value.split(":");
        onFilter({ sortBy, sortDirection: sortDirection as "asc" | "desc" });
    };

    const handleFilter = useCallback(() => {
        onFilter({
            categories: localSelectedCategories,
            brands: localSelectedBrands,
        });
        setIsOpen(false);
    }, [localSelectedCategories, localSelectedBrands, onFilter]);

    const FilterContent = () => (
        <div className="space-y-6">
            {categories.length > 0 && (
                <div className="space-y-4">
                    <h4 className="font-medium">الفئات</h4>
                    <div className="grid grid-cols-1 gap-3 pr-1">
                        {categories.map((category) => (
                            <div key={category.id} className="flex items-center space-x-2 gap-2">
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
                                <Label
                                    htmlFor={`category-${category.id}`}
                                    className="text-sm font-normal leading-none cursor-pointer"
                                >
                                    {category.name}
                                </Label>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {brands.length > 0 && (
                <div className="space-y-4">
                    <h4 className="font-medium">العلامات التجارية</h4>
                    <div className="grid grid-cols-1 gap-3 pr-1">
                        {brands.map((brand) => (
                            <div key={brand.id} className="flex items-center space-x-2 gap-2">
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
                                <Label
                                    htmlFor={`brand-${brand.id}`}
                                    className="text-sm font-normal leading-none cursor-pointer"
                                >
                                    {brand.name}
                                </Label>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );

    return (
        <div className={cn("flex items-center gap-2", "flex-col lg:flex-row")}>
            <Sheet open={isOpen} onOpenChange={setIsOpen}>
                <SheetTrigger asChild>
                    <Button variant="outline" size="sm" className="lg:hidden">
                        <Filter className="h-4 w-4 ml-2" />
                        تصفية
                    </Button>
                </SheetTrigger>
                <SheetContent side="right" className="w-full sm:max-w-lg">
                    <SheetHeader className="mb-6">
                        <SheetTitle>تصفية المنتجات</SheetTitle>
                    </SheetHeader>
                    <ScrollArea className="h-[calc(100vh-10rem)]">
                        <FilterContent />
                    </ScrollArea>
                    <div className="absolute bottom-0 left-0 right-0 p-6 bg-white dark:bg-gray-950 border-t">
                        <Button onClick={handleFilter} className="w-full">تطبيق</Button>
                    </div>
                </SheetContent>
            </Sheet>

            {isLg && (brands.length > 0 || categories.length > 0) && (
                <div className={cn(
                    "hidden lg:flex items-center gap-2 p-2",
                    "min-w-[200px] max-w-xs flex-1",
                    "border rounded-lg"
                )}>
                    <SlidersHorizontal className="h-4 w-4" />
                    <FilterContent />
                    <Button onClick={handleFilter} size="sm">تطبيق</Button>
                </div>
            )}

            <Select onValueChange={handleSort}>
                <SelectTrigger className="w-full lg:w-[180px]">
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
