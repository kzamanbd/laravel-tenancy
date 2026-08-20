import { cn } from '@/lib/utils';
import { useState, type ComponentProps } from 'react';

const sizes = {
    xs: 'size-6 text-[10px]',
    sm: 'size-8 text-xs',
    default: 'size-10 text-sm',
    lg: 'size-12 text-base',
    xl: 'size-16 text-lg',
};

// Round avatar container — compose with <AvatarFallback> (initials/icon) and
// <AvatarImage> (overlays when it loads).
export function Avatar({
    className,
    size = 'default',
    ...props
}: ComponentProps<'span'> & { size?: keyof typeof sizes }) {
    return (
        <span
            className={cn(
                'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-muted font-semibold text-muted-foreground select-none',
                sizes[size],
                className,
            )}
            {...props}
        />
    );
}

// Overlays the fallback; if the image errors it removes itself so the underlying
// <AvatarFallback> shows through.
export function AvatarImage({ src, alt, className, ...props }: ComponentProps<'img'>) {
    const [failed, setFailed] = useState(false);
    if (!src || failed) return null;
    return (
        <img
            src={src}
            alt={alt}
            loading='lazy'
            onError={() => setFailed(true)}
            className={cn('absolute inset-0 h-full w-full object-cover', className)}
            {...props}
        />
    );
}

// Always-rendered base layer (initials or an icon) sitting behind <AvatarImage>.
export function AvatarFallback({ className, ...props }: ComponentProps<'span'>) {
    return <span className={cn('flex h-full w-full items-center justify-center', className)} {...props} />;
}
