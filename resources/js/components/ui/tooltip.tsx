import { cn } from '@/lib/utils';
import * as TooltipPrimitive from '@radix-ui/react-tooltip';
import type { ComponentProps } from 'react';

// Place <TooltipProvider> once near the app root (or around a group of tooltips)
// to share delay/skip-delay timing.
export function TooltipProvider({ delayDuration = 200, ...props }: ComponentProps<typeof TooltipPrimitive.Provider>) {
    return <TooltipPrimitive.Provider delayDuration={delayDuration} {...props} />;
}

// Root of a single tooltip; supports `open` / `onOpenChange`.
export const Tooltip = TooltipPrimitive.Root;
// Element that toggles the tooltip on hover/focus. Defaults to a button; use `asChild`.
export const TooltipTrigger = TooltipPrimitive.Trigger;

// The floating tooltip panel. Portalled to <body>, token-styled, with simple
// open/closed transitions and an optional arrow.
export function TooltipContent({
    className,
    children,
    sideOffset = 4,
    arrow = false,
    ...props
}: ComponentProps<typeof TooltipPrimitive.Content> & { arrow?: boolean }) {
    return (
        <TooltipPrimitive.Portal>
            <TooltipPrimitive.Content
                sideOffset={sideOffset}
                className={cn(
                    'z-50 w-fit overflow-hidden rounded-md border border-border bg-popover px-3 py-1.5 text-xs text-popover-foreground shadow-sm',
                    'origin-(--radix-tooltip-content-transform-origin) transition data-[state=closed]:scale-95 data-[state=closed]:opacity-0 data-[state=delayed-open]:scale-100 data-[state=delayed-open]:opacity-100',
                    className,
                )}
                {...props}>
                {children}
                {arrow && <TooltipPrimitive.Arrow className='fill-popover' width={10} height={5} />}
            </TooltipPrimitive.Content>
        </TooltipPrimitive.Portal>
    );
}
