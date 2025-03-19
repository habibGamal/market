import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";

const sortOptions = [
    { label: "الأحدث", value: "created_at:desc" },
    { label: "الأقدم", value: "created_at:asc" },
    { label: "السعر: الأعلى للأقل", value: "packet_price:desc" },
    { label: "السعر: الأقل للأعلى", value: "packet_price:asc" },
    { label: "الاسم: أ-ي", value: "name:asc" },
    { label: "الاسم: ي-أ", value: "name:desc" },
];

interface SortingSelectProps {
    onSort: (value: string) => void;
}

export const SortingSelect = ({ onSort }: SortingSelectProps) => {
    return (
        <Select onValueChange={onSort}>
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
    );
};
