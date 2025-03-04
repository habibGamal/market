export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export interface Category {
    id: number;
    name: string;
    description?: string;
    image: string | null;
}
export interface CartItem {
    product: Product;
    packets: number;
    pieces: number;
}
export interface Brand {
    id: number;
    name: string;
    description?: string;
}

export interface ProductPrice {
    original?: number;
    discounted: number;
}

export interface ProductPrices {
    packet: ProductPrice;
    piece: ProductPrice;
}

export interface Product {
    id: number;
    name: string;
    description?: string;
    price: number;
    category_id: number;
    brand_id: number;
    category?: Category;
    brand?: Brand;
    created_at: string;
    updated_at: string;
    image: string | null;
    prices: ProductPrices;
    isNew?: boolean;
    isDeal?: boolean;
}

export interface SliderImage {
    src: string;
    alt: string;
    href?: string;
}

export interface HomeProps {
    sliderImages: SliderImage[];
    categories: Category[];
    products: Product[];
    canLogin: boolean;
    canRegister: boolean;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    auth: {
        user: User;
    };
};
