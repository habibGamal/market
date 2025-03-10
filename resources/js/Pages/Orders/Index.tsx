import React from "react";
import { Head, Link, router } from "@inertiajs/react";
import { PageTitle } from "@/Components/ui/page-title";
import { ShoppingBag, ChevronLeft, FileText, Package } from "lucide-react";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { formatDate } from "@/lib/utils";
import type { Order } from "@/types";
import { OrderStatusBadge } from "@/Components/Orders/OrderStatusBadge";

interface Props {
    orders: Order[];
}

export default function OrdersIndex({ orders }: Props) {
    return (
        <>
            <Head title="طلباتي" />
            <PageTitle>
                <ShoppingBag className="h-6 w-6 mr-2" />
                طلباتي
            </PageTitle>

            {orders.length === 0 ? (
                <div className="text-center py-16">
                    <FileText className="mx-auto h-12 w-12 text-secondary-400" />
                    <h3 className="mt-2 text-lg font-medium text-secondary-900">
                        لا توجد طلبات
                    </h3>
                    <p className="mt-1 text-sm text-secondary-500">
                        لم تقم بأي طلبات بعد
                    </p>
                    <div className="mt-6">
                        <Button
                            onClick={() => router.get("/")}
                            className="min-w-[200px]"
                        >
                            تصفح المنتجات
                        </Button>
                    </div>
                </div>
            ) : (
                <div className="divide-y bg-white rounded-lg shadow-sm">
                    {orders.map((order) => (
                        <Link
                            key={order.id}
                            href={`/orders/${order.id}`}
                            className="block hover:bg-secondary-50 transition-colors"
                        >
                            <div className="p-4 sm:p-6">
                                <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <span className="font-medium text-secondary-900">
                                                طلب #{order.id}
                                            </span>
                                            <OrderStatusBadge
                                                status={order.status}
                                            />
                                        </div>
                                        <div className="mt-1 text-sm text-secondary-500">
                                            {formatDate(order.created_at)}
                                        </div>
                                    </div>

                                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                                        <div className="flex items-center gap-2 text-secondary-700">
                                            <Package className="h-4 w-4" />
                                            <span className="text-sm">
                                                {order.items_count}{" "}
                                                {order.items_count &&
                                                order.items_count > 1
                                                    ? "أصناف"
                                                    : "صنف"}
                                            </span>
                                        </div>

                                        <div className="flex items-center justify-between sm:justify-start w-full sm:w-auto">
                                            <span className="font-medium text-secondary-900">
                                                {Number(order.total).toFixed(2)}{" "}
                                                ج.م
                                            </span>
                                            <ChevronLeft className="h-5 w-5 text-secondary-400 ms-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </>
    );
}
