import { Badge } from "@/Components/ui/badge";

interface ReturnStatusBadgeProps {
    status: string;
}

export function ReturnStatusBadge({ status }: ReturnStatusBadgeProps) {
    const getReturnStatusName = (status: string) => {
        switch (status) {
            case "pending":
                return "قيد الانتظار";
            case "approved":
                return "موافق عليه";
            case "driver_pickup":
                return "قيد الاستلام";
            case "received":
                return "تم الاستلام";
            case "rejected":
                return "مرفوض";
            default:
                return status;
        }
    };

    return (
        <Badge variant="outline" className="text-xs">
            {getReturnStatusName(status)}
        </Badge>
    );
}
