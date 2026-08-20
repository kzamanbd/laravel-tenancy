import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

export function Input({ className, type = 'text', ...props }: ComponentProps<'input'>) {
    return (
        <input
            type={type}
            className={cn(
                'flex h-9 w-full rounded-lg border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                className,
            )}
            {...props}
        />
    );
}
