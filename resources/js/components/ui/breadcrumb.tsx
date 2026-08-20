import { cn } from '@/lib/utils';
import * as Slot from '@radix-ui/react-slot';
import type { ComponentProps, ReactNode } from 'react';

// shadcn-style Breadcrumb root (<nav>). Compose with <BreadcrumbList>,
// <BreadcrumbItem>, <BreadcrumbLink>, <BreadcrumbPage> and <BreadcrumbSeparator>.
export function Breadcrumb({ className, ...props }: ComponentProps<'nav'>) {
    return <nav aria-label='breadcrumb' className={className} {...props} />;
}

export function BreadcrumbList({ className, ...props }: ComponentProps<'ol'>) {
    return (
        <ol
            className={cn(
                'flex min-w-0 flex-wrap items-center gap-1.5 text-sm font-medium text-muted-foreground',
                className,
            )}
            {...props}
        />
    );
}

export function BreadcrumbItem({ className, ...props }: ComponentProps<'li'>) {
    return <li className={cn('inline-flex items-center gap-1.5 truncate', className)} {...props} />;
}

// A clickable crumb. Defaults to <a>; use `asChild` to wrap Inertia's <Link>.
export function BreadcrumbLink({ className, asChild = false, ...props }: ComponentProps<'a'> & { asChild?: boolean }) {
    const Comp = asChild ? Slot.Root : 'a';
    return <Comp className={cn('transition-colors hover:text-foreground', className)} {...props} />;
}

// The current (non-clickable) crumb.
export function BreadcrumbPage({ className, ...props }: ComponentProps<'span'>) {
    return (
        <span
            role='link'
            aria-disabled='true'
            aria-current='page'
            className={cn('truncate text-foreground', className)}
            {...props}
        />
    );
}

// Decorative divider between crumbs. Defaults to a chevron; override via children.
export function BreadcrumbSeparator({ className, children, ...props }: ComponentProps<'li'>) {
    return (
        <li role='presentation' aria-hidden='true' className={cn('flex shrink-0 items-center', className)} {...props}>
            {(children as ReactNode) ?? <span className='icon-[mdi--chevron-right] size-4 rtl:rotate-180' />}
        </li>
    );
}
