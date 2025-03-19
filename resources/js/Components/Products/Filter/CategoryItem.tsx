import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { Checkbox } from "@/Components/ui/checkbox";
import { ChevronDown, ChevronLeft } from "lucide-react";
import { FilterOption } from "@/types";

interface CategoryItemProps {
    category: FilterOption;
    selectedCategories: number[];
    onToggleCategory: (categoryId: number, isChecked: boolean) => void;
    level?: number;
}

export const CategoryItem = ({
    category,
    selectedCategories,
    onToggleCategory,
    level = 0
}: CategoryItemProps) => {
    const [expanded, setExpanded] = useState(false);
    const hasChildren = category.children && category.children.length > 0;

    return (
        <div className="space-y-1">
            <div className="flex items-center gap-2">
                {hasChildren && (
                    <Button
                        variant="ghost"
                        size="icon"
                        className="h-5 w-5 p-0"
                        onClick={() => setExpanded(!expanded)}
                    >
                        {expanded ?
                            <ChevronDown className="h-4 w-4" /> :
                            <ChevronLeft className="h-4 w-4" />
                        }
                    </Button>
                )}
                {!hasChildren && <div className="w-5"></div>}
                <Checkbox
                    id={`category-${category.id}`}
                    checked={selectedCategories.includes(category.id)}
                    onCheckedChange={(checked) => {
                        onToggleCategory(category.id, !!checked);
                    }}
                />
                <Label
                    htmlFor={`category-${category.id}`}
                    className="text-sm font-normal cursor-pointer"
                >
                    {category.name}
                </Label>
            </div>

            {hasChildren && expanded && (
                <div className={`pr-4 border-r border-gray-200 dark:border-gray-700`}>
                    {category.children?.map(child => (
                        <CategoryItem
                            key={child.id}
                            category={child}
                            selectedCategories={selectedCategories}
                            onToggleCategory={onToggleCategory}
                            level={level + 1}
                        />
                    ))}
                </div>
            )}
        </div>
    );
};
