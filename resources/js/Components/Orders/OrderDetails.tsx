import { Card } from "@/Components/ui/card";
import { Calendar, Package, ShoppingBag } from "lucide-react";

interface OrderDetailsProps {
    createdAt: string;
    itemsCount: number;
    status: string;
}

export function OrderDetails({ createdAt, itemsCount, status }: OrderDetailsProps) {
    const getStatusName = (status: string) => {
        switch (status) {
            case "pending":
                return "قيد الانتظار";
            case "out_for_delivery":
                return "قيد التوصيل";
            case "delivered":
                return "تم التسليم";
            case "cancelled":
                return "ملغي";
            default:
                return status;
        }
    };

    return (
        <Card>
            <div className="p-6">
                <h3 className="text-lg font-medium text-secondary-900 mb-4">
                    تفاصيل الطلب
                </h3>
                <div className="space-y-3">
                    <div className="flex items-start gap-2">
                        <Calendar className="w-5 h-5 text-secondary-500 mt-0.5" />
                        <div>
                            <div className="text-sm font-medium text-secondary-900">
                                تاريخ الطلب
                            </div>
                            <div className="text-sm text-secondary-500">
                                {createdAt}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-start gap-2">
                        <Package className="w-5 h-5 text-secondary-500 mt-0.5" />
                        <div>
                            <div className="text-sm font-medium text-secondary-900">
                                عدد الأصناف
                            </div>
                            <div className="text-sm text-secondary-500">
                                {itemsCount} {itemsCount > 1 ? "أصناف" : "صنف"}
                            </div>
                        </div>
                    </div>

                    <div className="flex items-start gap-2">
                        <ShoppingBag className="w-5 h-5 text-secondary-500 mt-0.5" />
                        <div>
                            <div className="text-sm font-medium text-secondary-900">
                                حالة الطلب
                            </div>
                            <div className="text-sm text-secondary-500">
                                {getStatusName(status)}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    );
}
