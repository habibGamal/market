import { Badge } from "@/Components/ui/badge";

interface ReturnStatusBadgeProps {
    status: string;
}

export function ReturnStatusBadge({ status }: ReturnStatusBadgeProps) {
    const getReturnStatusName = (status: string) => {
        switch (status) {
            case "pending":
                return "قيد الانتظار";
            case "driver_pickup":
                return "السائق في الطريق للاستلام";
            case "received_from_customer":
                return "تم الاستلام";
            default:
                return status;
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case "pending":
                return "text-yellow-700 border-yellow-200 bg-yellow-100";
            case "driver_pickup":
                return "text-blue-700 border-blue-200 bg-blue-100";
            case "received_from_customer":
                return "text-green-700 border-green-200 bg-green-100";
            default:
                return "";
        }
    };

    return (
        <Badge variant="outline" className={`text-xs ${getStatusColor(status)}`}>
            {getReturnStatusName(status)}
        </Badge>
    );
}
