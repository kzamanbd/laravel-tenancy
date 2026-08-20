import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

// Joins a row of <Button>s: collapses inner radii + shared borders.
export function ButtonGroup({ className, ...props }: ComponentProps<'div'>) {
    return (
        <div
            role='group'
            className={cn(
                'inline-flex items-center [&>*:not(:first-child)]:-ml-px [&>*:not(:first-child)]:rounded-l-none [&>*:not(:last-child)]:rounded-r-none',
                className,
            )}
            {...props}
        />
    );
}
