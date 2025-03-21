import React from "react";
import { Head, router } from "@inertiajs/react";
import { PageTitle } from "@/Components/ui/page-title";
import { ShoppingBag, FileText } from "lucide-react";
import { Button } from "@/Components/ui/button";
import type { Order, Pagination } from "@/types";
import { EmptyState } from "@/Components/EmptyState";
import { OrderList } from "@/Components/Orders/OrderList";
import { PaginationLoadMore } from "@/Components/Products/PaginationLoadMore";
import { Skeleton } from "@/Components/ui/skeleton";

interface Props {
    orders: Pagination<Order>["data"];
    pagination: Pagination<Order>["pagination"];
}

export default function OrdersIndex({ orders, pagination }: Props) {
    const CardSkeleton = () => (
        <div className="space-y-2 min-w-[200px] h-[100px] my-4">
            <Skeleton className="w-full h-[100%] rounded-lg" />
        </div>
    );

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
                <>
                    <OrderList orders={orders} />
                    {orders && orders.length > 0 && (
                        <PaginationLoadMore
                            dataKey={"orders"}
                            paginationKey={"pagination"}
                            sectionKey={"page"}
                            currentPage={pagination.current_page}
                            nextPageUrl={pagination.next_page_url}
                            total={pagination.total}
                            LoadingSkeleton={CardSkeleton}
                        />
                    )}
                </>
            )}
        </>
    );
}
