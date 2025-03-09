import { Card } from "@/Components/ui/card";
import { RotateCcw } from "lucide-react";
import { ReturnStatusBadge } from "./ReturnStatusBadge";
import type { ReturnItem } from "@/types";

interface ReturnItemsProps {
    items: ReturnItem[];
}

export function ReturnItems({ items }: ReturnItemsProps) {
    if (items.length === 0) return null;

    return (
        <Card>
            <div className="p-6">
                <div className="flex items-center gap-2 mb-4">
                    <RotateCcw className="h-5 w-5 text-amber-500" />
                    <h3 className="text-lg font-medium text-secondary-900">
                        المرتجعات
                    </h3>
                </div>

                <div className="divide-y">
                    {items.map((item) => (
                        <div
                            key={item.id}
                            className="py-4 first:pt-0 last:pb-0"
                        >
                            <div className="flex justify-between">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <h4 className="font-medium text-secondary-900">
                                            {item.product.name}
                                        </h4>
                                        <ReturnStatusBadge status={item.status} />
                                    </div>
                                    <div className="mt-1 text-sm text-secondary-500">
                                        {item.packets_quantity > 0 && (
                                            <span className="block">
                                                {item.packets_quantity} عبوة
                                            </span>
                                        )}
                                        {item.piece_quantity > 0 && (
                                            <span className="block">
                                                {item.piece_quantity} قطعة
                                            </span>
                                        )}
                                        {item.return_reason && (
                                            <span className="block mt-1 text-amber-500">
                                                سبب الإرجاع: {item.return_reason}
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <div className="text-amber-600 font-medium">
                                    - {item.total} ج.م
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </Card>
    );
}
