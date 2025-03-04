import { Product } from "@/types";

export interface ProductsState {
    products: Product[];
    loading: boolean;
    hasMore: boolean;
    page: number;
    selectedCategories: number[];
    selectedBrands: number[];
    sortBy: string;
    sortDirection: 'asc' | 'desc';
}

export type ProductsAction =
    | { type: 'SET_LOADING'; payload: boolean }
    | { type: 'SET_PRODUCTS'; payload: Product[] }
    | { type: 'APPEND_PRODUCTS'; payload: Product[] }
    | { type: 'SET_HAS_MORE'; payload: boolean }
    | { type: 'SET_PAGE'; payload: number }
    | { type: 'SET_CATEGORIES'; payload: number[] }
    | { type: 'SET_BRANDS'; payload: number[] }
    | { type: 'SET_SORT'; payload: { sortBy: string; sortDirection: 'asc' | 'desc' } }
    | { type: 'RESET_FILTERS' };

export const initialState: ProductsState = {
    products: [],
    loading: false,
    hasMore: true,
    page: 1,
    selectedCategories: [],
    selectedBrands: [],
    sortBy: 'created_at',
    sortDirection: 'desc'
};

export function productsReducer(state: ProductsState, action: ProductsAction): ProductsState {
    switch (action.type) {
        case 'SET_LOADING':
            return { ...state, loading: action.payload };

        case 'SET_PRODUCTS':
            return { ...state, products: action.payload };

        case 'APPEND_PRODUCTS':
            return {
                ...state,
                products: [...state.products, ...action.payload]
            };

        case 'SET_HAS_MORE':
            return { ...state, hasMore: action.payload };

        case 'SET_PAGE':
            return { ...state, page: action.payload };

        case 'SET_CATEGORIES':
            return {
                ...state,
                selectedCategories: action.payload,
                page: 1,
                products: []
            };

        case 'SET_BRANDS':
            return {
                ...state,
                selectedBrands: action.payload,
                page: 1,
                products: []
            };

        case 'SET_SORT':
            return {
                ...state,
                sortBy: action.payload.sortBy,
                sortDirection: action.payload.sortDirection,
                page: 1,
                products: []
            };

        case 'RESET_FILTERS':
            return {
                ...state,
                selectedCategories: [],
                selectedBrands: [],
                sortBy: 'created_at',
                sortDirection: 'desc',
                page: 1,
                products: []
            };

        default:
            return state;
    }
}
