import { Badge } from "@/Components/ui/badge";

interface OrderStatusBadgeProps {
    status: string;
}

export function OrderStatusBadge({ status }: OrderStatusBadgeProps) {
    const getStatusColor = (status: string) => {
        switch (status) {
            case "pending":
                return "bg-yellow-100 text-yellow-800 border-yellow-200";
            case "out_for_delivery":
                return "bg-blue-100 text-blue-800 border-blue-200";
            case "delivered":
                return "bg-green-100 text-green-800 border-green-200";
            case "cancelled":
                return "bg-red-100 text-red-800 border-red-200";
            default:
                return "bg-gray-100 text-gray-800 border-gray-200";
        }
    };

    const getStatusName = (status: string) => {
        switch (status) {
            case "pending":
                return "قيد الانتظار";
            case "out_for_delivery":
                return "قيد التوصيل";
            case "delivered":
                return "تم التسليم";
            case "cancelled":
                return "ملغي";
            default:
                return status;
        }
    };

    return (
        <Badge variant="outline" className={getStatusColor(status)}>
            {getStatusName(status)}
        </Badge>
    );
}
