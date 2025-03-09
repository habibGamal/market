import { InputHTMLAttributes } from "react";

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {}

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
    packet_to_piece: number;
    category_id: number;
    brand_id: number;
    category?: Category;
    brand?: Brand;
    created_at: string;
    updated_at: string;
    image: string | null;
    prices: ProductPrices;
    is_new?: boolean;
    is_deal?: boolean;
}

export interface OrderItem {
    id: number;
    product: Product;
    packets_quantity: number;
    packet_price: number;
    piece_quantity: number;
    piece_price: number;
    total: number;
}

export interface CancelledItem {
    id: number;
    product: Product;
    packets_quantity: number;
    piece_quantity: number;
    total: number;
    notes: string;
}

export interface ReturnItem {
    id: number;
    product: Product;
    packets_quantity: number;
    piece_quantity: number;
    total: number;
    status: string;
    return_reason: string;
}

export interface Order {
    id: number;
    status: string;
    total: number;
    net_total: number;
    created_at: string;
    items: OrderItem[];
    cancelled_items: CancelledItem[];
    return_items: ReturnItem[];
    items_count?: number;
}

export interface SliderImage {
    src: string;
    alt: string;
    href?: string;
}

export type SectionLocation = "HOME" | "HOT_DEALS";
export type SectionType = "VIRTUAL" | "REAL";

export interface Section {
    id: number;
    title: string;
    active: boolean;
    sort_order: number;
    business_type_id: number;
    location: SectionLocation;
    section_type: SectionType;
    products: Product[];
    brands: Brand[];
    categories: Category[];
}

export interface HomeProps {
    sliderImages: SliderImage[];
    categories: Category[];
    sections: Pagination<Section>['data'];
    pagination: Pagination<Section>['pagination'];
    products: Product[];
    announcements: Array<{
        id: number;
        text: string;
        color: string;
    }>;
    canLogin: boolean;
    canRegister: boolean;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Pagination<T> {
    data: T[];
    pagination: {
        current_page: number;
        first_page_url: string;
        from: number;
        last_page: number;
        last_page_url: string;
        links: PaginationLink[];
        next_page_url: string | null;
        path: string;
        per_page: number;
        prev_page_url: string | null;
        to: number;
        total: number;
    };
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>
> = T & {
    auth: {
        user: User;
    };
};
