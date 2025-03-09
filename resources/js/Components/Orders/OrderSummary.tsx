import { Card } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";

interface OrderSummaryProps {
    total: number;
    netTotal: number;
    hasReturns: boolean;
}

export function OrderSummary({ total, netTotal, hasReturns }: OrderSummaryProps) {
    const returnsAmount = total - netTotal;

    return (
        <Card>
            <div className="p-6">
                <h3 className="text-lg font-medium text-secondary-900 mb-4">
                    ملخص الطلب
                </h3>
                <div className="space-y-2">
                    <div className="flex justify-between text-secondary-600">
                        <span>إجمالي الطلب</span>
                        <span>{total} ج.م</span>
                    </div>

                    {hasReturns && (
                        <div className="flex justify-between text-red-600">
                            <span>خصم المرتجعات</span>
                            <span>- {returnsAmount} ج.م</span>
                        </div>
                    )}

                    <Separator className="my-2" />

                    <div className="flex justify-between font-medium text-lg text-secondary-900">
                        <span>الإجمالي النهائي</span>
                        <span>{netTotal} ج.م</span>
                    </div>
                </div>
            </div>
        </Card>
    );
}
