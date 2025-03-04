import { useState } from "react";
import { Button } from "@/Components/ui/button";
import { CartItem } from "@/Components/Cart/CartItem";
import { ShoppingBag } from "lucide-react";
import { Head } from "@inertiajs/react";

// Fake data for demo purposes
const fakeCartItems = [
    {
        product: {
            id: 1,
            name: "حليب طازج",
            image: "https://images.unsplash.com/photo-1563636619-e9143da7973b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&h=400&q=80",
            prices: {
                packet: {
                    original: "25.00",
                    discounted: "20.00",
                },
                piece: {
                    original: "5.00",
                    discounted: "4.00",
                },
            },
        },
        packets: 2,
        pieces: 3,
    },
    {
        product: {
            id: 2,
            name: "خبز عربي",
            image: "https://images.unsplash.com/photo-1509440159596-0249088772ff?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=400&h=400&q=80",
            prices: {
                packet: {
                    original: "15.00",
                    discounted: "12.00",
                },
                piece: {
                    original: "3.00",
                    discounted: "2.50",
                },
            },
        },
        packets: 1,
        pieces: 4,
    },
];

export default function Cart() {
    const [cartItems, setCartItems] = useState(fakeCartItems);

    const updateQuantity = (index: number, packets: number, pieces: number) => {
        const newItems = [...cartItems];
        newItems[index] = {
            ...newItems[index],
            packets,
            pieces,
        };
        setCartItems(newItems);
    };

    const removeItem = (index: number) => {
        setCartItems(cartItems.filter((_, i) => i !== index));
    };

    const calculateTotal = () => {
        return cartItems.reduce((total, item) => {
            return (
                total +
                (item.packets *
                    parseFloat(item.product.prices.packet.discounted) +
                    item.pieces *
                        parseFloat(item.product.prices.piece.discounted))
            );
        }, 0);
    };

    if (cartItems.length === 0) {
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
            <h1 className="text-2xl font-bold text-secondary-900 mb-8">
                السلة
            </h1>

            <div className="grid md:grid-cols-3 gap-8">
                {/* Cart Items */}
                <div className="md:col-span-2 bg-white rounded-lg shadow-sm divide-y">
                    {cartItems.map((item, index) => (
                        <CartItem
                            key={item.product.id}
                            product={item.product}
                            packets={item.packets}
                            pieces={item.pieces}
                            onUpdateQuantity={(packets, pieces) =>
                                updateQuantity(index, packets, pieces)
                            }
                            onRemove={() => removeItem(index)}
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
                                <span>{calculateTotal().toFixed(2)} ج.م</span>
                            </div>
                        </div>
                        <Button className="w-full mt-6" size="lg">
                            إتمام الطلب
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
