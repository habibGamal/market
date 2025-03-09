import { WhenVisible } from "@inertiajs/react";
import { Skeleton } from "../ui/skeleton";
import { EmptyState } from "../EmptyState";

interface PaginationLoadMoreProps {
    dataKey: string;
    paginationKey: string;
    sectionKey: string;
    currentPage: number;
    nextPageUrl: string | null;
    total: number;
    LoadingSkeleton: React.ComponentType;
}

export function PaginationLoadMore({
    dataKey,
    paginationKey,
    sectionKey,
    currentPage,
    nextPageUrl,
    total,
    LoadingSkeleton,
}: PaginationLoadMoreProps) {
    return (
        <WhenVisible
            params={{
                only: [dataKey, paginationKey],
                data: {
                    [sectionKey]: currentPage + 1,
                },
                preserveUrl: true,
                onSuccess: (page) => {
                    window.history.state.page.props = page.props;
                },
            }}
            always={nextPageUrl !== null}
            fallback={<LoadingSkeleton />}
        >
            {nextPageUrl !== null ? (
                <LoadingSkeleton />
            ) : total === 0 ? (
                <EmptyState
                    title="لا توجد عناصر"
                    description="لم يتم العثور على عناصر في هذا القسم"
                />
            ) : (
                <></>
            )}
        </WhenVisible>
    );
}
