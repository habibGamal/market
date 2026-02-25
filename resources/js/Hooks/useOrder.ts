import { usePage, router } from "@inertiajs/react";
import axios from "axios";
import { useState } from "react";
import { toast } from "sonner";

export function useOrder() {
    const [loading, setLoading] = useState(false);

    const placeOrder = async (notes?: string) => {
        setLoading(true);
        try {
            const response = await axios.post('/orders', { notes });
            toast.success(response.data.message);
            // Redirect to the order details page
            router.visit(`/orders/${response.data.order_id}`);
            return response.data;
        } catch (error: any) {
            toast.error(error.response?.data?.message || "حدث خطأ أثناء إتمام الطلب");
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return {
        loading,
        placeOrder,
    };
}
