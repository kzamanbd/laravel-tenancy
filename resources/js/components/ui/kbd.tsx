import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

// Styled keyboard key for shortcut hints.
export function Kbd({ className, ...props }: ComponentProps<'kbd'>) {
    return (
        <kbd
            className={cn(
                'inline-flex h-5 min-w-5 items-center justify-center rounded border border-border bg-muted px-1.5 font-mono text-[11px] font-medium text-muted-foreground',
                className,
            )}
            {...props}
        />
    );
}
