import { useState } from "react";
import { cn } from "@/lib/utils";

type FallbackImageProps = Omit<React.ImgHTMLAttributes<HTMLImageElement>, 'src'> & {
    src: string | null;
    fallbackSrc?: string;
};

export function FallbackImage({
    src,
    fallbackSrc = '/images/placeholder.svg',
    className,
    alt = '',
    ...props
}: FallbackImageProps) {
    const [imgSrc, setImgSrc] = useState(src || fallbackSrc);

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
