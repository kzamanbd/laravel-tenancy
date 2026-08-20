import { cn } from '@/lib/utils';
import * as AccordionPrimitive from '@radix-ui/react-accordion';
import type { ComponentProps } from 'react';

// shadcn-style wrapper over Radix Accordion. Supports type="single" (with
// `collapsible`) or type="multiple", controlled via `value` / `onValueChange`.
export function Accordion({ className, ...props }: ComponentProps<typeof AccordionPrimitive.Root>) {
    return <AccordionPrimitive.Root className={cn('w-full', className)} {...props} />;
}

export function AccordionItem({ className, ...props }: ComponentProps<typeof AccordionPrimitive.Item>) {
    return <AccordionPrimitive.Item className={cn('border-b border-border', className)} {...props} />;
}

// Header trigger with a chevron that rotates when open.
export function AccordionTrigger({ className, children, ...props }: ComponentProps<typeof AccordionPrimitive.Trigger>) {
    return (
        <AccordionPrimitive.Header className='flex'>
            <AccordionPrimitive.Trigger
                className={cn(
                    'flex flex-1 items-center justify-between py-4 text-sm font-medium text-foreground transition-all hover:underline focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none [&[data-state=open]>span:last-child]:rotate-180',
                    className,
                )}
                {...props}>
                {children}
                <span className='icon-[mdi--chevron-down] size-4 shrink-0 text-muted-foreground transition-transform duration-200' />
            </AccordionPrimitive.Trigger>
        </AccordionPrimitive.Header>
    );
}

// Animated open/close height via the Radix CSS var (see base/radix.css).
export function AccordionContent({ className, children, ...props }: ComponentProps<typeof AccordionPrimitive.Content>) {
    return (
        <AccordionPrimitive.Content
            className='accordion-content overflow-hidden text-sm text-muted-foreground'
            {...props}>
            <div className={cn('pt-0 pb-4', className)}>{children}</div>
        </AccordionPrimitive.Content>
    );
}
