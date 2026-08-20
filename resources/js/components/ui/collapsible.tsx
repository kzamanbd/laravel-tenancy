import { cn } from '@/lib/utils';
import * as CollapsiblePrimitive from '@radix-ui/react-collapsible';
import type { ComponentProps } from 'react';

// shadcn-style wrapper over Radix Collapsible. Supports `open` / `onOpenChange`,
// `defaultOpen` and `disabled`.
export const Collapsible = CollapsiblePrimitive.Root;
export const CollapsibleTrigger = CollapsiblePrimitive.Trigger;

// Animated open/close height via the Radix CSS var (see base/radix.css).
export function CollapsibleContent({
    className,
    children,
    ...props
}: ComponentProps<typeof CollapsiblePrimitive.Content>) {
    return (
        <CollapsiblePrimitive.Content className='collapsible-content overflow-hidden text-sm' {...props}>
            <div className={cn(className)}>{children}</div>
        </CollapsiblePrimitive.Content>
    );
}
