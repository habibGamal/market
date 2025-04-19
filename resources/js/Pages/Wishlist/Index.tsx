import { EmptyState } from "@/Components/EmptyState";
import { ProductCard } from "@/Components/Products/ProductCard";
import { Button } from "@/Components/ui/button";
import { PageTitle } from "@/Components/ui/page-title";
import { Product } from "@/types";
import { Head, Link, router } from "@inertiajs/react";
import { Heart, Trash } from "lucide-react";

export default function Index({ products }: { products: Product[] }) {
    const handleRemoveFromWishlist = (productId: number) => {
        router.delete(route("wishlist.destroy", productId));
    };

    return (
        <>
            <Head title="قائمة المفضلة" />

            <PageTitle>
                <Heart className="h-6 w-6 mr-2" />
                قائمة المفضلة
            </PageTitle>

            {products.length === 0 ? (
                <EmptyState
                    icon={Heart}
                    title="قائمة المفضلة فارغة"
                    description="لم تقم بإضافة أي منتجات إلى قائمة المفضلة بعد"
                    actions={
                        <Link href={"/"}>
                            <Button>تصفح المنتجات</Button>
                        </Link>
                    }
                />
            ) : (
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    {products.map((product) => (
                        <>
                            <ProductCard
                                key={product.id}
                                product={product}
                                additionalActions={
                                    <Button
                                        variant="outline"
                                        size="icon"
                                        onClick={() =>
                                            handleRemoveFromWishlist(product.id)
                                        }
                                    >
                                        <Trash size={20} />
                                    </Button>
                                }
                            />
                        </>
                    ))}
                </div>
            )}
        </>
    );
}
