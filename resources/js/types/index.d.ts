import { InputHTMLAttributes } from "react";

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {}

export interface Area {
    id: number;
    name: string;
    has_village: boolean;
    city_id: number;
    created_at: string;
    updated_at: string;
}

export interface City {
    id: number;
    name: string;
    gov_id: number;
    created_at: string;
    updated_at: string;
    areas: Area[];
}

export interface Governorate {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
    cities: City[];
}

export interface User {
    id: number;
    name: string;
    email: string;
    phone: string;
    address: string;
    email_verified_at?: string;
    gov_id?: number;
    city_id?: number;
    area_id?: number;
    village?: string;
    location?: string;
    whatsapp?: string;
    business_type_id?: number;
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
    image: string | null;
    description?: string;
}

export interface FilterOption {
    id: number;
    name: string;
    parent_id?: number;
    children?: FilterOption[];
}

export interface ProductFilters {
    categories?: number[];
    brands?: number[];
    sortBy?: string;
    sortDirection?: "asc" | "desc";
    minPrice?: number;
    maxPrice?: number;
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
    can_sell_pieces: boolean;
    is_new?: boolean;
    is_deal?: boolean;
    is_active?: boolean;
    has_stock?: boolean;
    packet_alter_name: string;
    piece_alter_name: string;
    isInWishlist?: boolean;
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
    order_id: number;
    created_at: string;
    updated_at: string;
}

export interface Offer {
    id: number;
    name: string;
}

export interface Order {
    id: number;
    status: string;
    total: number;
    net_total: number;
    discount: number;
    offers: Offer[];
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

export interface NotificationData {
    type: string;
    order_id?: string;
    order_code?: string;
    url?: string;
    action_url?: string;
    [key: string]: any;
}

export interface Notification {
    id: string;
    type: "order" | "delivery" | "promotion" | "status" | "general" | "order-items-cancelled";
    title: string;
    description: string;
    date: string;
    isRead: boolean;
    data?: NotificationData;
}
