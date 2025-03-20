import { Card } from "@/Components/ui/card";
import { OrderStatusBadge } from "./OrderStatusBadge";
import type { OrderItem } from "@/types";
import { FallbackImage } from "../ui/fallback-image";

interface OrderItemsProps {
    items: OrderItem[];
    status: string;
}

export function OrderItems({ items, status }: OrderItemsProps) {
    return (
        <Card>
            <div className="p-6">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-medium text-secondary-900">
                        الأصناف المطلوبة
                    </h3>
                    <OrderStatusBadge status={status} />
                </div>

                <div className="divide-y">
                    {items.map((item) => (
                        <div
                            key={item.id}
                            className="py-4 first:pt-0 last:pb-0"
                        >
                            <div className="flex items-start">
                                {item.product.image && (
                                    <div className="flex-shrink-0 w-16 h-16 bg-secondary-100 rounded-md overflow-hidden">
                                        <FallbackImage
                                            src={item.product.image}
                                            alt={item.product.name}
                                            className="w-full h-full object-cover"
                                        />
                                    </div>
                                )}
                                <div className="flex-grow mr-4">
                                    <h4 className="font-medium text-secondary-900">
                                        {item.product.name}
                                    </h4>
                                    <div className="mt-1 text-sm text-secondary-500 space-y-1">
                                        {item.packets_quantity > 0 && (
                                            <div>
                                                {item.packets_quantity} عبوة ×{" "}
                                                {item.packet_price} ج.م
                                            </div>
                                        )}
                                        {item.piece_quantity > 0 && (
                                            <div>
                                                {item.piece_quantity} قطعة ×{" "}
                                                {item.piece_price} ج.م
                                            </div>
                                        )}
                                    </div>
                                </div>
                                <div className="flex-shrink-0 text-left">
                                    <span className="font-medium text-secondary-900">
                                        {item.total} ج.م
                                    </span>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </Card>
    );
}
