import { ImageSlider } from "@/Components/ImageSlider/ImageSlider";
import { CategoryCarousel } from "@/Components/Categories/CategoryCarousel";
import { ProductsSection } from "@/Components/Products/ProductsSection";
import { HomeProps } from "@/types";

export default function Home({
    sliderImages,
    categories,
    products,
}: HomeProps) {
    const handleAddToCart = (id: number, packets: number, pieces: number) => {
        // Implement your cart logic here
        console.log("Adding to cart:", { id, packets, pieces });
    };

    return (
        <div className="animate-section-x">
            <section className="mb-8 ltr">
                <ImageSlider images={sliderImages} />
            </section>

            <section className="mb-8">
                <CategoryCarousel categories={categories} />
            </section>

            <ProductsSection
                title="المنتجات"
                limit={30}
            />
        </div>
    );
}
