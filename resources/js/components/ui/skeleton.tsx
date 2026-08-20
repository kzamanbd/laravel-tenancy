import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

// Pulsing placeholder block — size it with utility classes.
export function Skeleton({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('animate-pulse rounded-md bg-muted', className)} {...props} />;
}
