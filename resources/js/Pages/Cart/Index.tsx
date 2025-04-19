import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { CartItem } from "@/Components/Cart/CartItem";
import { ShoppingBag } from "lucide-react";
import { Head, router } from "@inertiajs/react";
import { EmptyState } from "@/Components/EmptyState";
import { useCart } from "@/Hooks/useCart";
import type { Product } from "@/types";
import { PageTitle } from "@/Components/ui/page-title";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { AlertCircle } from "lucide-react";

interface Props {
    cart: {
        items: Array<{
            id: number;
            product: Product;
            packets_quantity: number;
            piece_quantity: number;
            total: number;
            errorMsg?: string;
        }>;
        total: number;
    };
    errors: Record<string, string>;
}

export default function Cart({ cart, errors }: Props) {
    const [items, setItems] = useState(cart.items);
    const [total, setTotal] = useState(cart.total);
    const { loading: cartLoading, updateQuantity, removeItem } = useCart();

    const handleUpdateQuantity = async (
        id: number,
        packets: number,
        pieces: number
    ) => {
        try {
            const response = await updateQuantity(id, packets, pieces);
            console.log(response);
            const newItems = items.map((item) =>
                item.id === id
                    ? {
                          ...response.item,
                          errorMsg: undefined,
                      }
                    : item
            );
            setItems([...newItems]);
            setTotal(response.cart_total);
        } catch (error: any) {
            const errorMsg = error.response?.data?.message || "حدث خطأ ما";
            setItems(
                items.map((item) =>
                    item.id === id ? { ...item, errorMsg } : item
                )
            );
        }
    };

    const handleRemoveItem = async (id: number) => {
        try {
            const response = await removeItem(id);
            setItems(items.filter((item) => item.id !== id));
            setTotal(response.cart_total);
        } catch (error) {
            // Error is handled by the hook
        }
    };

    if (items.length === 0) {
        return (
            <>
                <Head title="السلة" />

                <PageTitle>
                    <ShoppingBag className="h-6 w-6 mr-2" />
                    السلة
                </PageTitle>

                <EmptyState
                    icon={ShoppingBag}
                    title="السلة فارغة"
                    description="لم تقم بإضافة أي منتجات إلى السلة بعد"
                    actions={
                        <Button
                            onClick={() => router.get("/")}
                            className="min-w-[200px]"
                        >
                            تصفح المنتجات
                        </Button>
                    }
                />
            </>
        );
    }

    return (
        <>
            <Head title="السلة" />
            <PageTitle>
                <ShoppingBag className="h-6 w-6 mr-2" />
                السلة
            </PageTitle>

            <div className="grid md:grid-cols-3 gap-8">
                {/* Cart Items */}
                <div className="md:col-span-2 bg-white rounded-lg shadow-sm divide-y">
                    {items.map((item) => (
                        <CartItem
                            key={item.id}
                            id={item.id}
                            product={item.product}
                            packets={item.packets_quantity}
                            pieces={item.piece_quantity}
                            total={item.total}
                            errorMsg={item.errorMsg}
                            onUpdateQuantity={(packets, pieces) =>
                                handleUpdateQuantity(item.id, packets, pieces)
                            }
                            onRemove={() => handleRemoveItem(item.id)}
                            loading={cartLoading}
                        />
                    ))}
                </div>
                {/* Errors section */}
                {Object.keys(errors).length > 0 && (
                    <Alert variant="destructive" className="mb-4" dir="rtl">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>
                            <ul className="list-disc list-inside">
                                {Object.entries(errors).map(([key, error]) => (
                                    <li key={key}>{error}</li>
                                ))}
                            </ul>
                        </AlertDescription>
                    </Alert>
                )}

                {/* Order Summary */}
                <div className="space-y-6">
                    <div className="bg-white rounded-lg shadow-sm p-6">
                        <h2 className="text-lg font-medium text-secondary-900 mb-4">
                            ملخص الطلب
                        </h2>
                        <div className="space-y-4">
                            <div className="flex justify-between text-base font-medium text-secondary-900">
                                <span>المجموع</span>
                                <span>{Number(total).toFixed(2)} ج.م</span>
                            </div>
                        </div>
                        <Button
                            className="w-full mt-6"
                            size="lg"
                            onClick={() => router.get("/place-order")}
                            disabled={cartLoading}
                        >
                            متابعة الطلب
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
