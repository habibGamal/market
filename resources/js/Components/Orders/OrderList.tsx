import type { Order } from "@/types";
import { OrderListItem } from "@/Components/Orders/OrderListItem";

interface OrderListProps {
    orders: Order[];
}

export function OrderList({ orders }: OrderListProps) {
    return (
        <div className="divide-y bg-white rounded-lg shadow-sm">
            {orders.map((order) => (
                <OrderListItem key={order.id} order={order} />
            ))}
        </div>
    );
}
