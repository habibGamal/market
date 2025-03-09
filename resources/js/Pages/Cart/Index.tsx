import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { CartItem } from "@/Components/Cart/CartItem";
import { ShoppingBag } from "lucide-react";
import { Head, router } from "@inertiajs/react";
import { useCart } from "@/hooks/useCart";
import { useOrder } from "@/hooks/useOrder";
import type { Product } from "@/types";
import { PageTitle } from "@/Components/ui/page-title";

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
}

export default function Cart({ cart }: Props) {
    const [items, setItems] = useState(cart.items);
    const [total, setTotal] = useState(cart.total);
    const { loading: cartLoading, updateQuantity, removeItem } = useCart();
    const { loading: orderLoading, placeOrder } = useOrder();

    const isLoading = cartLoading || orderLoading;

    const handleUpdateQuantity = async (
        id: number,
        packets: number,
        pieces: number
    ) => {
        try {
            const response = await updateQuantity(id, packets, pieces);
            const newItems = items.map((item) =>
                item.id === id
                    ? {
                          ...item,
                          packets_quantity: packets,
                          piece_quantity: pieces,
                          errorMsg: undefined,
                      }
                    : item
            );
            setItems(newItems);
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

    const handlePlaceOrder = async () => {
        try {
            await placeOrder();
        } catch (error) {
            // Error is handled by the hook
        }
    };

    if (items.length === 0) {
        return (
            <>
                <Head title="السلة" />
                <div className="container mx-auto px-4 py-16">
                    <div className="text-center">
                        <ShoppingBag className="mx-auto h-12 w-12 text-secondary-400" />
                        <h3 className="mt-2 text-lg font-medium text-secondary-900">
                            السلة فارغة
                        </h3>
                        <p className="mt-1 text-sm text-secondary-500">
                            لم تقم بإضافة أي منتجات إلى السلة بعد
                        </p>
                        <div className="mt-6">
                            <Button href="/products" className="min-w-[200px]">
                                تصفح المنتجات
                            </Button>
                        </div>
                    </div>
                </div>
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
                            loading={isLoading}
                        />
                    ))}
                </div>

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
                            onClick={handlePlaceOrder}
                            disabled={isLoading}
                        >
                            {orderLoading ? "جاري إتمام الطلب..." : "إتمام الطلب"}
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
