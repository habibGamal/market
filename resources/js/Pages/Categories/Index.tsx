import { Head } from "@inertiajs/react";
import { Link } from "@inertiajs/react";
import { Card, CardContent } from "@/Components/ui/card";
import { FallbackImage } from "@/Components/ui/fallback-image";
import { Category } from "@/types";
import { PageTitle } from "@/Components/ui/page-title";

interface Props {
    categories: Category[];
}

export default function Index({ categories }: Props) {
    return (
        <>
            <Head title="الفئات" />
            <PageTitle>الفئات</PageTitle>
            <div className="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 md:gap-4">
                {categories.map((category) => (
                    <Link
                        key={category.id}
                        href={`/categories/${category.id}`}
                    >
                        <Card className="group hover:shadow-lg transition-all duration-300 border-0 !bg-transparent shadow-none">
                            <CardContent className="p-0 ">
                                <div className="text-center">
                                    <div className="relative aspect-square overflow-hidden rounded-lg mb-2">
                                        <FallbackImage
                                            src={category.image}
                                            alt={category.name}
                                            className="transition-transform group-hover:scale-110"
                                        />
                                    </div>
                                    <span className="text-sm block pb-2 font-medium text-secondary-900">
                                        {category.name}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </Link>
                ))}
            </div>
        </>
    );
}
