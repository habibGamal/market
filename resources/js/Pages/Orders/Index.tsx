import React from "react";
import { Head, router } from "@inertiajs/react";
import { PageTitle } from "@/Components/ui/page-title";
import { ShoppingBag, FileText } from "lucide-react";
import { Button } from "@/Components/ui/button";
import type { Order } from "@/types";
import { EmptyState } from "@/Components/EmptyState";
import { OrderList } from "@/Components/Orders/OrderList";

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
                <EmptyState
                    icon={FileText}
                    title="لا توجد طلبات"
                    description="لم تقم بأي طلبات بعد"
                    actions={
                        <Button
                            onClick={() => router.get("/")}
                            className="min-w-[200px]"
                        >
                            تصفح المنتجات
                        </Button>
                    }
                />
            ) : (
                <OrderList orders={orders} />
            )}
        </>
    );
}
