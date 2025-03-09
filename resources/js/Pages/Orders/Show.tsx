import React from "react";
import { Head, Link } from "@inertiajs/react";
import { PageTitle } from "@/Components/ui/page-title";
import { ShoppingBag, ChevronRight } from "lucide-react";
import { OrderItems } from "@/Components/Orders/OrderItems";
import { CancelledItems } from "@/Components/Orders/CancelledItems";
import { ReturnItems } from "@/Components/Orders/ReturnItems";
import { OrderSummary } from "@/Components/Orders/OrderSummary";
import { OrderDetails } from "@/Components/Orders/OrderDetails";
import type { Order } from "@/types";

interface Props {
    order: Order;
}

export default function OrderShow({ order }: Props) {
    return (
        <>
            <Head title={`طلب #${order.id}`} />

            <div className="flex items-center mb-6 space-x-4 space-x-reverse">
                <Link href="/orders" className="flex items-center text-primary-600 hover:text-primary-700">
                    <ChevronRight className="h-5 w-5 ml-1" />
                    <span>العودة إلى الطلبات</span>
                </Link>
            </div>

            <PageTitle>
                <ShoppingBag className="h-6 w-6 mr-2" />
                طلب #{order.id}
            </PageTitle>

            {/* Order Details */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="lg:col-span-2 space-y-6">
                    {/* Order Items */}
                    <OrderItems items={order.items} status={order.status} />

                    {/* Cancelled Items */}
                    <CancelledItems items={order.cancelled_items} />

                    {/* Returned Items */}
                    <ReturnItems items={order.return_items} />
                </div>

                <div className="space-y-6">
                    {/* Order Summary */}
                    <OrderSummary
                        total={order.total}
                        netTotal={order.net_total}
                        hasReturns={order.return_items.length > 0}
                    />

                    {/* Order Details */}
                    <OrderDetails
                        createdAt={order.created_at}
                        itemsCount={order.items.length}
                        status={order.status}
                    />
                </div>
            </div>
        </>
    );
}
