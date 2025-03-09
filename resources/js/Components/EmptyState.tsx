import { cn } from "@/lib/utils";
import { InboxIcon, LucideIcon } from "lucide-react";

interface EmptyStateProps {
    title: string;
    description: string;
    actions?: React.ReactNode;
    icon?: LucideIcon;
    className?: string;
}

export function EmptyState({
    title,
    description,
    actions,
    icon: Icon = InboxIcon,
    className
}: EmptyStateProps) {
    return (
        <div
            className={cn(
                "flex flex-col items-center justify-center mx-auto",
                "min-h-[250px] p-8 text-center",
                "rounded-xl border border-dashed",
                "shadow-sm backdrop-blur-sm",
                "transition-all duration-200 ease-in-out",
                "hover:shadow-md hover:border-muted-foreground/30",
                className
            )}
        >
            {Icon && (
                <div className="mb-4 rounded-full bg-muted p-4 ring-1 ring-border">
                    <Icon className="h-8 w-8 text-muted-foreground" />
                </div>
            )}
            <h3 className="text-xl font-semibold tracking-tight mb-2">
                {title}
            </h3>
            <p className="text-sm text-muted-foreground max-w-[24rem]">
                {description}
            </p>
            {actions && (
                <div className="mt-6 flex gap-3 items-center justify-center">
                    {actions}
                </div>
            )}
        </div>
    );
}
