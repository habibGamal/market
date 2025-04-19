import { useState } from "react";
import { cn } from "@/lib/utils";

type FallbackImageProps = Omit<
    React.ImgHTMLAttributes<HTMLImageElement>,
    "src"
> & {
    src: string | null;
    fallbackSrc?: string;
};

export function FallbackImage({
    src,
    fallbackSrc = "/images/output-onlinepngtools.png",
    className,
    alt = "",
    ...props
}: FallbackImageProps) {
    const resolvedPath = src?.startsWith("http") ? src : `/storage/${src}`;
    const [imgSrc, setImgSrc] = useState(resolvedPath || fallbackSrc);

    const handleImageError = () => {
        setImgSrc(fallbackSrc);
    };

    return (
        <img
            src={imgSrc}
            alt={alt}
            className={cn("object-cover w-full h-full", className)}
            onError={handleImageError}
            {...props}
        />
    );
}
