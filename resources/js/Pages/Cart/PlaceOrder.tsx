import { Button } from "@/Components/ui/button";
import { Head } from "@inertiajs/react";
import { ShoppingBag } from "lucide-react";
import { PageTitle } from "@/Components/ui/page-title";
import type { Product } from "@/types";
import { useOrder } from "@/Hooks/useOrder";

interface OrderItem {
    product: Product;
    packets_quantity: number;
    piece_quantity: number;
    total: number;
}

interface Props {
    preview: {
        items: OrderItem[];
        subtotal: number;
        discount: number;
        total: number;
        applied_offers: Array<{
            id: number;
            name: string;
            description: string;
        }>;
    };
}

export default function PlaceOrder({ preview }: Props) {
    const { loading: orderLoading, placeOrder } = useOrder();

    const handlePlaceOrder = async () => {
        try {
            await placeOrder();
        } catch (error) {
            // Error is handled by the hook
        }
    };

    return (
        <>
            <Head title="تأكيد الطلب" />
            <PageTitle>
                <ShoppingBag className="h-6 w-6 mr-2" />
                تأكيد الطلب
            </PageTitle>

            <div className="grid md:grid-cols-3 gap-8">
                {/* Order Items */}
                <div className="md:col-span-2 bg-white rounded-lg shadow-sm divide-y">
                    {preview.items.map((item, index) => (
                        <div key={index} className="p-4 flex items-start space-x-4 space-x-reverse">
                            <div className="flex-1 min-w-0">
                                <h3 className="text-sm font-medium text-secondary-900">
                                    {item.product.name}
                                </h3>
                                <div className="mt-1 text-sm text-secondary-500">
                                    <div>العبوات: {item.packets_quantity}</div>
                                    <div>القطع: {item.piece_quantity}</div>
                                </div>
                            </div>
                            <div className="text-sm font-medium text-secondary-900">
                                {item.total.toFixed(2)} ج.م
                            </div>
                        </div>
                    ))}
                </div>

                {/* Order Summary */}
                <div className="space-y-6">
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <h2 className="text-lg font-medium text-secondary-900 mb-4">
                            ملخص الطلب
                        </h2>
                        <div className="space-y-4">
                            <div className="flex justify-between text-sm text-secondary-600">
                                <span>المجموع الفرعي</span>
                                <span>{preview.subtotal.toFixed(2)} ج.م</span>
                            </div>

                            {preview.discount > 0 && (
                                <div className="flex justify-between text-sm text-green-600">
                                    <span>الخصم</span>
                                    <span>- {preview.discount.toFixed(2)} ج.م</span>
                                </div>
                            )}

                            <div className="flex justify-between text-base font-medium text-secondary-900 pt-4 border-t">
                                <span>الإجمالي</span>
                                <span>{preview.total.toFixed(2)} ج.م</span>
                            </div>
                        </div>

                        {preview.applied_offers.length > 0 && (
                            <div className="mt-4 pt-4 border-t">
                                <h3 className="text-sm font-medium text-secondary-900 mb-2">
                                    العروض المطبقة
                                </h3>
                                <ul className="text-sm text-secondary-600 space-y-2">
                                    {preview.applied_offers.map((offer) => (
                                        <li key={offer.id}>• {offer.name}</li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        <Button
                            className="w-full mt-6"
                            size="lg"
                            onClick={handlePlaceOrder}
                            disabled={orderLoading}
                        >
                            {orderLoading ? "جاري تأكيد الطلب..." : "تأكيد الطلب"}
                        </Button>
                    </div>

                    <div className="bg-secondary-50 rounded-lg p-6">
                        <h3 className="text-sm font-medium text-secondary-900 mb-4">
                            معلومات هامة
                        </h3>
                        <ul className="text-sm text-secondary-500 space-y-2">
                            <li>• الأسعار تشمل ضريبة القيمة المضافة</li>
                            <li>• التوصيل خلال 24-48 ساعة</li>
                            <li>• الدفع عند الاستلام متاح</li>
                        </ul>
                    </div>
                </div>
            </div>
        </>
    );
}
