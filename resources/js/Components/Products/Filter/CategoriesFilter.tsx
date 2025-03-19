import { useMemo } from "react";
import { Label } from "@/Components/ui/label";
import { FilterOption } from "@/types";
import { CategoryItem } from "./CategoryItem";

const organizeCategoriesHierarchy = (categories: FilterOption[]): FilterOption[] => {
    const categoryMap = new Map<number, FilterOption>();
    const rootCategories: FilterOption[] = [];

    categories.forEach(category => {
        categoryMap.set(category.id, { ...category, children: [] });
    });

    categories.forEach(category => {
        const current = categoryMap.get(category.id);
        if (current) {
            if (category.parent_id === -1 || category.parent_id === undefined) {
                rootCategories.push(current);
            } else {
                const parent = categoryMap.get(category.parent_id);
                if (parent) {
                    if (!parent.children) {
                        parent.children = [];
                    }
                    parent.children.push(current);
                }
            }
        }
    });

    return rootCategories;
};

interface CategoriesFilterProps {
    categories: FilterOption[];
    selectedCategories: number[];
    onToggleCategory: (categoryId: number, isChecked: boolean) => void;
}

export const CategoriesFilter = ({ categories, selectedCategories, onToggleCategory }: CategoriesFilterProps) => {
    const hierarchicalCategories = useMemo(() => {
        return organizeCategoriesHierarchy(categories);
    }, [categories]);

    if (categories.length === 0) {
        return (
            <div className="space-y-4">
                <Label>الفئات</Label>
                <div className="text-center py-4 text-muted-foreground">
                    لا توجد فئات متاحة
                </div>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            <Label>الفئات</Label>
            <div className="space-y-2">
                {hierarchicalCategories.map((category) => (
                    <CategoryItem
                        key={category.id}
                        category={category}
                        selectedCategories={selectedCategories}
                        onToggleCategory={onToggleCategory}
                    />
                ))}
            </div>
        </div>
    );
};
