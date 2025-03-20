import React from "react";
import { Head } from "@inertiajs/react";
import { PageTitle } from "@/Components/ui/page-title";
import { RotateCcw } from "lucide-react";
import { Pagination, ReturnItem } from "@/types";
import { EmptyState } from "@/Components/EmptyState";
import { ReturnsList } from "@/Components/Returns/ReturnsList";
import { PaginationLoadMore } from "@/Components/Products/PaginationLoadMore";
import { Skeleton } from "@/Components/ui/skeleton";

interface Props {
    returns: Pagination<ReturnItem>["data"];
    pagination: Pagination<ReturnItem>["pagination"];
}

export default function Index({ returns, pagination }: Props) {
    const CardSkeleton = () => (
        <div className="space-y-2 min-w-[200px] h-[100px] my-4">
            <Skeleton className="w-full h-[100%] rounded-lg" />
        </div>
    );

    return (
        <>
            <Head title="المرتجعات" />
            <PageTitle>
                <RotateCcw className="h-6 w-6 mr-2" />
                المرتجعات
            </PageTitle>
            {returns.length === 0 ? (
                <EmptyState
                    icon={RotateCcw}
                    title="لا توجد مرتجعات"
                    description="لم تقم بإرجاع أي منتجات بعد"
                />
            ) : (
                <ReturnsList returns={returns} pagination={pagination} />
            )}
            {returns && returns.length > 0 && (
                <PaginationLoadMore
                    dataKey={"returns"}
                    paginationKey={"pagination"}
                    sectionKey={"page"}
                    currentPage={pagination.current_page}
                    nextPageUrl={pagination.next_page_url}
                    total={pagination.total}
                    LoadingSkeleton={CardSkeleton}
                />
            )}
        </>
    );
}
