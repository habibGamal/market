import { Link } from "@inertiajs/react";
import { ChevronLeft, Package } from "lucide-react";
import type { Order } from "@/types";
import { OrderStatusBadge } from "./OrderStatusBadge";
import { formatDate } from "@/lib/utils";

interface OrderListItemProps {
    order: Order;
}

export function OrderListItem({ order }: OrderListItemProps) {
    return (
        <Link
            href={`/orders/${order.id}`}
            className="block hover:bg-secondary-50 transition-colors"
        >
            <div className="p-4 sm:p-6">
                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div className="flex items-center gap-2">
                            <span className="font-medium text-secondary-900">
                                طلب #{order.id}
                            </span>
                            <OrderStatusBadge status={order.status} />
                        </div>
                        <div className="mt-1 text-sm text-secondary-500">
                            {formatDate(order.created_at)}
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                        <div className="flex items-center gap-2 text-secondary-700">
                            <Package className="h-4 w-4" />
                            <span className="text-sm">
                                {order.items_count}{" "}
                                {order.items_count && order.items_count > 1
                                    ? "أصناف"
                                    : "صنف"}
                            </span>
                        </div>

                        <div className="flex items-center justify-between sm:justify-start w-full sm:w-auto">
                            <div className="flex flex-col items-end">
                                <div className="font-medium text-secondary-900">
                                    {Number(order.net_total).toFixed(2)} ج.م
                                </div>
                            </div>
                            <ChevronLeft className="h-5 w-5 text-secondary-400 ms-2" />
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    );
}
