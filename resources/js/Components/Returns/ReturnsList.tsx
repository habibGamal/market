import { Pagination, ReturnItem } from "@/types";
import { ReturnListItem } from "./ReturnListItem";

interface ReturnsListProps {
    // returns: ReturnItem[];

    returns: Pagination<ReturnItem>["data"];
    pagination: Pagination<ReturnItem>["pagination"];
}

export function ReturnsList({ returns }: ReturnsListProps) {
    return (
        <div className="divide-y bg-white rounded-lg shadow-sm">
            {returns.map((item) => (
                <ReturnListItem key={item.id} item={item} />
            ))}
        </div>
    );
}
