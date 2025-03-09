import { useState } from "react";
import { toast } from "sonner";
import axios from "axios";

interface UseCartProps {
    onSuccess?: () => void;
}

export function useCart({ onSuccess }: UseCartProps = {}) {
    const [packets, setPackets] = useState(0);
    const [pieces, setPieces] = useState(0);
    const [loading, setLoading] = useState(false);

    const addToCart = async (productId: number) => {
        try {
            setLoading(true);
            const response = await axios.post('/cart', {
                product_id: productId,
                packets,
                pieces,
            });
            toast.success(response.data.message);
            // Reset form
            setPackets(0);
            setPieces(0);
            // Call success callback if provided
            onSuccess?.();
            return response.data;
        } catch (error: any) {
            toast.error(error.response?.data?.message || 'حدث خطأ ما');
            // throw error;
        } finally {
            setLoading(false);
        }
    };

    const updateQuantity = async (itemId: number, packets: number, pieces: number) => {
        try {
            setLoading(true);
            const response = await axios.patch(`/cart/${itemId}`, { packets, pieces });
            toast.success(response.data.message);
            return response.data;
        } catch (error: any) {
            toast.error(error.response?.data?.message || 'حدث خطأ ما');
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const removeItem = async (itemId: number) => {
        try {
            setLoading(true);
            const response = await axios.delete(`/cart/${itemId}`);
            toast.success(response.data.message);
            return response.data;
        } catch (error: any) {
            toast.error(error.response?.data?.message || 'حدث خطأ ما');
            throw error;
        } finally {
            setLoading(false);
        }
    };

    const emptyCart = async () => {
        try {
            setLoading(true);
            const response = await axios.delete('/cart');
            toast.success(response.data.message);
            return response.data;
        } catch (error: any) {
            toast.error(error.response?.data?.message || 'حدث خطأ ما');
            throw error;
        } finally {
            setLoading(false);
        }
    };

    return {
        packets,
        setPackets,
        pieces,
        setPieces,
        loading,
        addToCart,
        updateQuantity,
        removeItem,
        emptyCart,
    };
}
