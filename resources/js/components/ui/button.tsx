import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import * as Slot from '@radix-ui/react-slot';
import type { ComponentProps } from 'react';

// shadcn-style Button, styled with the project's design tokens.
// Variant set covers the full legacy .btn-* palette (solid / outline / soft /
// light / ghost / link) so the migration is lossless. Renders a <button> by
// default; use `asChild` to render links (e.g. Inertia's <Link>).
export const buttonVariants = cva(
    'inline-flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-transparent text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground hover:bg-primary/90',
                secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
                success: 'bg-green-600 text-white hover:bg-green-700',
                warning: 'bg-amber-400 text-amber-950 hover:bg-amber-500',
                info: 'bg-sky-600 text-white hover:bg-sky-700',
                destructive: 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
                outline: 'border-input bg-background text-foreground hover:bg-accent hover:text-accent-foreground',
                'outline-primary': 'border-primary text-primary hover:bg-primary hover:text-primary-foreground',
                'outline-secondary': 'border-border text-foreground hover:bg-secondary hover:text-secondary-foreground',
                'outline-success':
                    'border-green-600 text-green-700 hover:bg-green-600 hover:text-white dark:text-green-400',
                'outline-warning':
                    'border-amber-500 text-amber-700 hover:bg-amber-400 hover:text-amber-950 dark:text-amber-400',
                'outline-info': 'border-sky-600 text-sky-700 hover:bg-sky-600 hover:text-white dark:text-sky-400',
                'outline-destructive':
                    'border-destructive text-destructive hover:bg-destructive hover:text-destructive-foreground',
                'outline-light': 'border-border text-foreground hover:bg-accent hover:text-accent-foreground',
                'soft-primary': 'bg-primary/10 text-primary hover:bg-primary/20',
                'soft-secondary': 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
                'soft-success': 'bg-green-500/15 text-green-700 hover:bg-green-500/25 dark:text-green-300',
                'soft-warning': 'bg-amber-500/15 text-amber-700 hover:bg-amber-500/25 dark:text-amber-300',
                'soft-info': 'bg-sky-500/15 text-sky-700 hover:bg-sky-500/25 dark:text-sky-300',
                'soft-destructive': 'bg-destructive/10 text-destructive hover:bg-destructive/20 dark:text-red-300',
                light: 'border-border bg-background text-foreground hover:bg-accent hover:text-accent-foreground',
                ghost: 'text-foreground hover:bg-accent hover:text-accent-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
            },
            size: {
                xs: 'h-7 rounded-md px-2 text-xs',
                sm: 'h-8 rounded-md px-3 text-xs',
                default: 'h-9 px-4 py-2',
                lg: 'h-10 rounded-md px-6 text-base',
                xl: 'h-12 rounded-md px-8 text-lg',
                icon: 'size-9',
            },
        },
        defaultVariants: { variant: 'default', size: 'default' },
    },
);

export type ButtonProps = ComponentProps<'button'> &
    VariantProps<typeof buttonVariants> & {
        asChild?: boolean;
    };

export function Button({ className, variant, size, asChild = false, type = 'button', ...props }: ButtonProps) {
    const Comp = asChild ? Slot.Root : 'button';
    // Only emit a native `type` when we actually render a <button>.
    return (
        <Comp {...(asChild ? {} : { type })} className={cn(buttonVariants({ variant, size }), className)} {...props} />
    );
}
