import { Category, Brand } from "@/types";
import { Button } from "@/Components/ui/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { ProductsAction } from "./productsReducer";

interface ProductFiltersProps {
    categories: Category[];
    brands: Brand[];
    selectedCategories: number[];
    selectedBrands: number[];
    sortBy: string;
    sortDirection: 'asc' | 'desc';
    dispatch: React.Dispatch<ProductsAction>;
    limit?: number | null;
    onShowAll?: () => void;
}

export function ProductFilters({
    categories,
    brands,
    selectedCategories,
    selectedBrands,
    sortBy,
    sortDirection,
    dispatch,
    limit,
    onShowAll
}: ProductFiltersProps) {
    return (
        <div className="flex flex-wrap gap-2 items-center">
            {categories.length > 0 && (
                <Select
                    onValueChange={(value: string) => {
                        dispatch({
                            type: 'SET_CATEGORIES',
                            payload: value ? [parseInt(value)] : []
                        });
                    }}
                    value={selectedCategories[0]?.toString()}
                >
                    <SelectTrigger className="w-[180px]">
                        <SelectValue placeholder="تصنيف المنتجات" />
                    </SelectTrigger>
                    <SelectContent>
                        {categories.map((category) => (
                            <SelectItem key={category.id} value={category.id.toString()}>
                                {category.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            {brands.length > 0 && (
                <Select
                    onValueChange={(value: string) => {
                        dispatch({
                            type: 'SET_BRANDS',
                            payload: value ? [parseInt(value)] : []
                        });
                    }}
                    value={selectedBrands[0]?.toString()}
                >
                    <SelectTrigger className="w-[180px]">
                        <SelectValue placeholder="العلامة التجارية" />
                    </SelectTrigger>
                    <SelectContent>
                        {brands.map((brand) => (
                            <SelectItem key={brand.id} value={brand.id.toString()}>
                                {brand.name}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            )}

            <Select
                onValueChange={(value: string) => {
                    const [sortBy, sortDirection] = value.split(':');
                    dispatch({
                        type: 'SET_SORT',
                        payload: {
                            sortBy,
                            sortDirection: sortDirection as 'asc' | 'desc'
                        }
                    });
                }}
                value={`${sortBy}:${sortDirection}`}
            >
                <SelectTrigger className="w-[180px]">
                    <SelectValue placeholder="ترتيب حسب" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="created_at:desc">الأحدث أولاً</SelectItem>
                    <SelectItem value="created_at:asc">الأقدم أولاً</SelectItem>
                    <SelectItem value="packet_price:asc">السعر: من الأقل إلى الأعلى</SelectItem>
                    <SelectItem value="packet_price:desc">السعر: من الأعلى إلى الأقل</SelectItem>
                    <SelectItem value="name:asc">الاسم: أ-ي</SelectItem>
                    <SelectItem value="name:desc">الاسم: ي-أ</SelectItem>
                </SelectContent>
            </Select>

            {limit && onShowAll && (
                <Button
                    variant="outline"
                    onClick={onShowAll}
                    className="whitespace-nowrap"
                >
                    عرض الكل
                </Button>
            )}
        </div>
    );
}
