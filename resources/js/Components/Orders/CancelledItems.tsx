import { Card } from "@/Components/ui/card";
import { AlertCircle } from "lucide-react";
import type { CancelledItem } from "@/types";

interface CancelledItemsProps {
    items: CancelledItem[];
}

export function CancelledItems({ items }: CancelledItemsProps) {
    if (items.length === 0) return null;

    return (
        <Card>
            <div className="p-6">
                <div className="flex items-center gap-2 mb-4">
                    <AlertCircle className="h-5 w-5 text-red-500" />
                    <h3 className="text-lg font-medium text-secondary-900">
                        الأصناف الملغية
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
                                    <h4 className="font-medium text-secondary-900">
                                        {item.product.name}
                                    </h4>
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
                                        {item.notes && (
                                            <span className="block mt-1 text-red-500">
                                                ملاحظة: {item.notes}
                                            </span>
                                        )}
                                    </div>
                                </div>
                                <div className="text-red-600 font-medium">
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
