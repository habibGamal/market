import { Card } from "@/Components/ui/card";
import { Separator } from "@/Components/ui/separator";

interface OrderSummaryProps {
    total: number;
    netTotal: number;
    hasReturns: boolean;
    discount?: number;
    offers?: Array<{ id: number; name: string }>;
}

export function OrderSummary({ total, netTotal, hasReturns, discount, offers }: OrderSummaryProps) {
    const returnsAmount = total - netTotal;

    return (
        <Card>
            <div className="p-6">
                <h3 className="text-lg font-medium text-secondary-900 mb-4">
                    ملخص الطلب
                </h3>
                <div className="space-y-2">
                    <div className="flex justify-between text-sm text-secondary-600">
                        <span>المجموع الفرعي</span>
                        <span>{Number(total).toFixed(2)} ج.م</span>
                    </div>

                    {discount && discount > 0 && (
                        <div className="flex justify-between text-sm text-green-600">
                            <span>الخصم</span>
                            <span>- {Number(discount).toFixed(2)} ج.م</span>
                        </div>
                    )}

                    {hasReturns && (
                        <div className="flex justify-between text-sm text-red-600">
                            <span>خصم المرتجعات</span>
                            <span>- {Number(returnsAmount).toFixed(2)} ج.م</span>
                        </div>
                    )}

                    <Separator className="my-2" />

                    <div className="flex justify-between font-medium text-base text-secondary-900">
                        <span>الإجمالي النهائي</span>
                        <span>{Number(netTotal).toFixed(2)} ج.م</span>
                    </div>
                </div>

                {offers && offers.length > 0 && (
                    <div className="mt-4 pt-4 border-t">
                        <h3 className="text-sm font-medium text-secondary-900 mb-2">
                            العروض المطبقة
                        </h3>
                        <ul className="text-sm text-secondary-600 space-y-2">
                            {offers.map((offer) => (
                                <li key={offer.id}>• {offer.name}</li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </Card>
    );
}
