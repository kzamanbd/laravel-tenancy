import { cn } from '@/lib/utils';
import * as AlertDialogPrimitive from '@radix-ui/react-alert-dialog';
import type { ComponentProps } from 'react';

// shadcn-style AlertDialog root (confirmation dialog). Forwards `open` /
// `onOpenChange` / `defaultOpen` to the Radix primitive.
export const AlertDialog = AlertDialogPrimitive.Root;
// Use `asChild` to wrap a <Button>.
export const AlertDialogTrigger = AlertDialogPrimitive.Trigger;

// shadcn-style centered confirmation content — Portal + Overlay + Content with
// focus trap. No close (X) button — confirm via Action/Cancel.
export function AlertDialogContent({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Content>) {
    return (
        <AlertDialogPrimitive.Portal>
            <AlertDialogPrimitive.Overlay className='fixed inset-0 z-50 bg-black/60 backdrop-blur-sm data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0' />
            <AlertDialogPrimitive.Content
                className={cn(
                    'fixed top-1/2 left-1/2 z-50 grid w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 rounded-xl border border-border bg-card p-6 text-card-foreground shadow-md duration-200 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95',
                    className,
                )}
                {...props}
            />
        </AlertDialogPrimitive.Portal>
    );
}

// Groups the title + description.
export function AlertDialogHeader({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('flex flex-col gap-1.5 text-center sm:text-left', className)} {...props} />;
}

// Accessible heading (wires aria-labelledby).
export function AlertDialogTitle({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Title>) {
    return (
        <AlertDialogPrimitive.Title
            className={cn('text-lg leading-none font-semibold tracking-tight text-foreground', className)}
            {...props}
        />
    );
}

// Accessible body text (wires aria-describedby).
export function AlertDialogDescription({
    className,
    ...props
}: ComponentProps<typeof AlertDialogPrimitive.Description>) {
    return <AlertDialogPrimitive.Description className={cn('text-sm text-muted-foreground', className)} {...props} />;
}

// Action row, right-aligned on desktop.
export function AlertDialogFooter({ className, ...props }: ComponentProps<'div'>) {
    return <div className={cn('flex flex-col-reverse gap-2 sm:flex-row sm:justify-end', className)} {...props} />;
}

// Confirm button — styled like the default <Button>.
export function AlertDialogAction({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Action>) {
    return (
        <AlertDialogPrimitive.Action
            className={cn(
                'inline-flex h-9 items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium whitespace-nowrap text-primary-foreground transition-colors hover:bg-primary/90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none',
                className,
            )}
            {...props}
        />
    );
}

// Dismiss button — styled like the outline <Button>.
export function AlertDialogCancel({ className, ...props }: ComponentProps<typeof AlertDialogPrimitive.Cancel>) {
    return (
        <AlertDialogPrimitive.Cancel
            className={cn(
                'inline-flex h-9 items-center justify-center gap-2 rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium whitespace-nowrap text-foreground transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none',
                className,
            )}
            {...props}
        />
    );
}
