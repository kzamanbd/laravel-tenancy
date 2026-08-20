import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import type { ComponentProps } from 'react';

// shadcn-style Alert, styled with the project's design tokens.
// Compose with <AlertTitle> / <AlertDescription>.
export const alertVariants = cva('relative flex w-full items-start gap-3 rounded-lg border px-4 py-3 text-sm', {
    variants: {
        variant: {
            default: 'border-border bg-card text-card-foreground',
            primary: 'border-primary/20 bg-primary/10 text-primary',
            destructive:
                'border-destructive/20 bg-destructive/10 text-destructive dark:bg-destructive/15 dark:text-red-300',
            success: 'border-green-500/20 bg-green-500/10 text-green-700 dark:bg-green-500/15 dark:text-green-300',
            warning: 'border-amber-500/20 bg-amber-500/10 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
            info: 'border-sky-500/20 bg-sky-500/10 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
        },
    },
    defaultVariants: { variant: 'default' },
});

export function Alert({ className, variant, ...props }: ComponentProps<'div'> & VariantProps<typeof alertVariants>) {
    return <div role='alert' className={cn(alertVariants({ variant }), className)} {...props} />;
}

export function AlertTitle({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('mb-0.5 font-medium tracking-tight', className)} {...props} />;
}

export function AlertDescription({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('text-sm opacity-90 [&_p]:leading-relaxed', className)} {...props} />;
}
