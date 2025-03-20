import { Link } from "@inertiajs/react";
import { ChevronLeft, Package } from "lucide-react";
import { ReturnStatusBadge } from "@/Components/Orders/ReturnStatusBadge";
import { ReturnItem } from "@/types";
import { formatDate } from "@/lib/utils";

interface ReturnListItemProps {
    item: ReturnItem;
}

export function ReturnListItem({ item }: ReturnListItemProps) {
    return (
        <Link
            href={`/orders/${item.order_id}`}
            className="block hover:bg-secondary-50 transition-colors"
        >
            <div className="p-4 sm:p-6">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div className="flex justify-between items-center gap-2">
                            <span className="font-medium text-secondary-900">
                                مرتجع طلب #{item.order_id}
                            </span>
                            <ReturnStatusBadge status={item.status} />
                        </div>
                        <div className="mt-1 text-sm text-secondary-500">
                            {formatDate(item.created_at)}
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                        <div className="flex items-center gap-2 text-secondary-700">
                            <Package className="h-4 w-4" />
                            <span className="text-sm">
                                <span className="block">{item.product.name}</span>
                                {item.packets_quantity > 0 && (
                                    <span>
                                        {item.packets_quantity}{" "}
                                        باكيت{" "}
                                    </span>
                                )}
                                {item.piece_quantity > 0 && (
                                    <span>
                                        {item.piece_quantity}{" "}
                                        قطعة
                                    </span>
                                )}
                            </span>
                        </div>

                        <div className="flex items-center justify-between sm:justify-start w-full sm:w-auto">
                            <div className="flex flex-col items-end">
                                <div className="font-medium text-secondary-900">
                                    {Number(item.total).toFixed(2)} ج.م
                                </div>
                            </div>
                            <ChevronLeft className="h-5 w-5 text-secondary-400 ms-2" />
                        </div>
                    </div>
                </div>
                <div className="mt-2 text-sm text-amber-600">
                    سبب الإرجاع: {item.return_reason}
                </div>
            </div>
        </Link>
    );
}
