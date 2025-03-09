interface PageTitleProps {
    children: React.ReactNode;
}

export function PageTitle({ children }: PageTitleProps) {
    return (
        <h1 className="flex items-center gap-2 text-2xl font-bold mb-8 text-primary">
            {children}
        </h1>
    );
}
