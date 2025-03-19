import { Button } from "@/Components/ui/button";
import { Filter } from "lucide-react";
import { DialogTrigger } from "@/Components/ui/dialog";

interface FilterButtonProps {
    activeFiltersCount: number;
}

export const FilterButton = ({ activeFiltersCount }: FilterButtonProps) => {
    return (
        <DialogTrigger asChild>
            <Button variant="outline" className="relative">
                <Filter className="h-4 w-4 ml-2" />
                تصفية
                {activeFiltersCount > 0 && (
                    <span className="absolute -top-2 -right-2 min-w-[18px] h-[18px] rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center">
                        {activeFiltersCount}
                    </span>
                )}
            </Button>
        </DialogTrigger>
    );
};
