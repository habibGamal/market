import { Head } from "@inertiajs/react";
import { ProductsSection } from "@/Components/Products/ProductsSection";

export default function HotDeals() {
    return (
        <>
            <Head title="العروض المميزة" />

            <div className="container mx-auto px-4 py-8">
                <h1 className="flex items-center gap-2 text-2xl font-bold mb-8 text-primary">
                    <span>🔥</span>
                    <span>العروض المميزة</span>
                </h1>

                <ProductsSection
                    title=""
                    sortBy="packet_price"
                    sortDirection="asc"
                    showFilters={true}
                    onlyDeals={true}
                    limit={null}
                />
            </div>
        </>
    );
}
