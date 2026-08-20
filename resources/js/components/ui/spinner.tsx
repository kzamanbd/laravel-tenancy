import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

const sizes = { sm: 'size-4', default: 'size-6', lg: 'size-8' };

// Inline loading indicator; inherits colour from `text-*` (defaults to primary).
export function Spinner({
    className,
    size = 'default',
    ...props
}: ComponentProps<'span'> & { size?: keyof typeof sizes }) {
    return (
        <span
            role='status'
            className={cn(
                'inline-block animate-spin rounded-full border-2 border-current border-t-transparent text-primary',
                sizes[size],
                className,
            )}
            {...props}>
            <span className='sr-only'>Loading…</span>
        </span>
    );
}
