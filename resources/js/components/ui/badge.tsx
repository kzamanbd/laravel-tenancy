import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import type { ComponentProps } from 'react';

// shadcn-style Badge, styled with the project's design tokens.
// Rich variant set so legacy .badge-* classes map 1:1.
export const badgeVariants = cva(
    'inline-flex items-center justify-center gap-1 border font-medium whitespace-nowrap transition-colors',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground',
                secondary: 'border-transparent bg-secondary text-secondary-foreground',
                success: 'border-transparent bg-green-600 text-white',
                warning: 'border-transparent bg-amber-400 text-amber-950',
                info: 'border-transparent bg-sky-600 text-white',
                destructive: 'border-transparent bg-destructive text-destructive-foreground',
                dark: 'border-transparent bg-gray-800 text-white',
                light: 'border-transparent bg-muted text-foreground dark:text-foreground',
                outline: 'border-border text-foreground',
                'outline-primary': 'border-primary text-primary',
                'outline-success': 'border-green-600 text-green-700 dark:border-green-500 dark:text-green-400',
                'outline-warning': 'border-amber-500 text-amber-700 dark:text-amber-400',
                'outline-info': 'border-sky-600 text-sky-700 dark:border-sky-500 dark:text-sky-400',
                'outline-destructive': 'border-destructive text-destructive',
                'soft-primary': 'border-transparent bg-primary/10 text-primary',
                'soft-secondary': 'border-transparent bg-secondary text-secondary-foreground',
                'soft-success': 'border-transparent bg-green-500/15 text-green-700 dark:text-green-300',
                'soft-warning': 'border-transparent bg-amber-500/15 text-amber-700 dark:text-amber-300',
                'soft-info': 'border-transparent bg-sky-500/15 text-sky-700 dark:text-sky-300',
                'soft-destructive': 'border-transparent bg-destructive/10 text-destructive dark:text-red-300',
                'soft-dark': 'border-transparent bg-muted text-foreground dark:text-foreground',
            },
            size: {
                sm: 'px-1.5 py-0.5 text-[10px] leading-3',
                default: 'px-2.5 py-0.5 text-xs',
                lg: 'px-3 py-1 text-sm',
            },
            pill: { true: 'rounded-full', false: 'rounded-md' },
        },
        defaultVariants: { variant: 'default', size: 'default', pill: false },
    },
);

export type BadgeProps = ComponentProps<'span'> & VariantProps<typeof badgeVariants>;

export function Badge({ className, variant, size, pill = false, ...props }: BadgeProps) {
    return <span className={cn(badgeVariants({ variant, size, pill }), className)} {...props} />;
}
