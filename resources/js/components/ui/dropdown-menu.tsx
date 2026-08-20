import { cn } from '@/lib/utils';
import * as DropdownMenuPrimitive from '@radix-ui/react-dropdown-menu';
import type { ComponentProps } from 'react';

// Root provider; supports `open` / `onOpenChange`.
export const DropdownMenu = DropdownMenuPrimitive.Root;
// Pass `asChild` to wrap a <Button>.
export const DropdownMenuTrigger = DropdownMenuPrimitive.Trigger;
export const DropdownMenuGroup = DropdownMenuPrimitive.Group;
export const DropdownMenuSub = DropdownMenuPrimitive.Sub;

// Portals the menu and applies popover styling.
export function DropdownMenuContent({
    className,
    align = 'center',
    side = 'bottom',
    sideOffset = 4,
    ...props
}: ComponentProps<typeof DropdownMenuPrimitive.Content>) {
    return (
        <DropdownMenuPrimitive.Portal>
            <DropdownMenuPrimitive.Content
                align={align}
                side={side}
                sideOffset={sideOffset}
                className={cn(
                    'z-50 min-w-[8rem] overflow-hidden rounded-md border border-border bg-popover p-1 text-popover-foreground shadow-sm',
                    'data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95',
                    'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
                    className,
                )}
                {...props}
            />
        </DropdownMenuPrimitive.Portal>
    );
}

const itemClass =
    'relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm transition-colors outline-none select-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50';

// Fires `onSelect`; pass `inset` for label-aligned padding.
export function DropdownMenuItem({
    className,
    inset = false,
    ...props
}: ComponentProps<typeof DropdownMenuPrimitive.Item> & { inset?: boolean }) {
    return <DropdownMenuPrimitive.Item className={cn(itemClass, inset && 'pl-8', className)} {...props} />;
}

// Non-interactive section heading; pass `inset` to align with inset items.
export function DropdownMenuLabel({
    className,
    inset = false,
    ...props
}: ComponentProps<typeof DropdownMenuPrimitive.Label> & { inset?: boolean }) {
    return (
        <DropdownMenuPrimitive.Label
            className={cn('px-2 py-1.5 text-sm font-semibold text-foreground', inset && 'pl-8', className)}
            {...props}
        />
    );
}

// Thin divider between item groups.
export function DropdownMenuSeparator({ className, ...props }: ComponentProps<typeof DropdownMenuPrimitive.Separator>) {
    return <DropdownMenuPrimitive.Separator className={cn('-mx-1 my-1 h-px bg-border', className)} {...props} />;
}

// Submenu trigger — renders a chevron and opens the nested content on hover.
export function DropdownMenuSubTrigger({
    className,
    inset = false,
    children,
    ...props
}: ComponentProps<typeof DropdownMenuPrimitive.SubTrigger> & { inset?: boolean }) {
    return (
        <DropdownMenuPrimitive.SubTrigger
            className={cn(itemClass, 'data-[state=open]:bg-accent', inset && 'pl-8', className)}
            {...props}>
            {children}
            <span className='ms-auto icon-[mdi--chevron-right] size-4 rtl:rotate-180' aria-hidden='true' />
        </DropdownMenuPrimitive.SubTrigger>
    );
}

export function DropdownMenuSubContent({
    className,
    sideOffset = 4,
    ...props
}: ComponentProps<typeof DropdownMenuPrimitive.SubContent>) {
    return (
        <DropdownMenuPrimitive.Portal>
            <DropdownMenuPrimitive.SubContent
                sideOffset={sideOffset}
                className={cn(
                    'z-50 min-w-[8rem] overflow-hidden rounded-md border border-border bg-popover p-1 text-popover-foreground shadow-sm',
                    'data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95',
                    className,
                )}
                {...props}
            />
        </DropdownMenuPrimitive.Portal>
    );
}
