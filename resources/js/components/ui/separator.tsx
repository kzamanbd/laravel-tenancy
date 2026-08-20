import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

// Token-coloured divider, horizontal or vertical.
export function Separator({
    className,
    orientation = 'horizontal',
    ...props
}: ComponentProps<'div'> & { orientation?: 'horizontal' | 'vertical' }) {
    return (
        <div
            role='separator'
            aria-orientation={orientation}
            className={cn('shrink-0 bg-border', orientation === 'vertical' ? 'h-full w-px' : 'h-px w-full', className)}
            {...props}
        />
    );
}
