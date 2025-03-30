import { Head } from "@inertiajs/react";
import { Link } from "@inertiajs/react";
import { Card, CardContent } from "@/Components/ui/card";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Brand, Category } from "@/types";
import { PageTitle } from "@/Components/ui/page-title";
import { Button } from "@/Components/ui/button";
import { ChevronRight } from "lucide-react";
import { EmptyState } from "@/Components/EmptyState";

interface Props {
    category: Category;
    brands: Brand[];
}

export default function Show({ category, brands }: Props) {
    return (
        <>
            <Head title={category.name} />
            <div className="mb-6">
                <div className="flex items-center gap-2 mb-2">
                    <Link href="/categories">
                        <Button variant="ghost" size="sm" className="flex items-center gap-1">
                            <ChevronRight className="h-4 w-4" />
                            <span>الفئات</span>
                        </Button>
                    </Link>
                </div>
                <PageTitle>{category.name}</PageTitle>
            </div>

            {brands.length > 0 ? (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-4">
                    {brands.map((brand) => (
                        <Link
                            key={brand.id}
                            href={`/product-list?model=brand&id=${brand.id}`}
                        >
                            <Card className="group hover:shadow-lg transition-all duration-300 border-0">
                                <CardContent className="p-2 md:p-4">
                                    <div className="text-center">
                                        <div className="relative aspect-square overflow-hidden rounded-lg mb-2 flex items-center justify-center bg-gray-100">
                                            {brand.image ? (
                                                <FallbackImage
                                                    src={brand.image}
                                                    alt={brand.name}
                                                    className="transition-transform group-hover:scale-110"
                                                />
                                            ) : (
                                                <div className="text-xl font-bold text-secondary-500">{brand.name.charAt(0)}</div>
                                            )}
                                        </div>
                                        <span className="text-sm font-medium text-secondary-900">
                                            {brand.name}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            ) : (
                <EmptyState
                    title="لا توجد علامات تجارية"
                    description="لا توجد علامات تجارية في هذه الفئة. يمكنك العودة إلى الفئات واختيار فئة أخرى"
                />
            )}
        </>
    );
}
