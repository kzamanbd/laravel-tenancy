import { DialogSectionedContext, useDialogSectioned } from '@/components/ui/dialog-sectioned';
import { cn } from '@/lib/utils';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import type { ComponentProps } from 'react';

// shadcn-style Dialog root. Forwards open-state (`open` / `onOpenChange`).
export const Dialog = DialogPrimitive.Root;
export const DialogTrigger = DialogPrimitive.Trigger;
export const DialogClose = DialogPrimitive.Close;

export type DialogContentProps = ComponentProps<typeof DialogPrimitive.Content> & {
    sectioned?: boolean;
    centered?: boolean;
};

/**
 * shadcn-style centered modal content — Portal + Overlay + Content with focus
 * trap, Esc-to-close, and a close button.
 *
 * `sectioned` switches to the app-modal layout: the content becomes a flex column with
 * no padding and a capped height, and DialogHeader / DialogBody / DialogFooter pick up
 * their dividers + padding automatically. Compose those sections instead of repeating
 * border/padding utilities on every modal.
 *
 * Vertical placement: top-aligned by default (the modal sits near the top of the
 * viewport); pass `centered` to vertically centre it instead.
 */
export function DialogContent({ className, children, sectioned, centered, ...props }: DialogContentProps) {
    return (
        <DialogPrimitive.Portal>
            <DialogPrimitive.Overlay className='fixed inset-0 z-50 bg-black/60 backdrop-blur-sm data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0' />
            <DialogPrimitive.Content
                className={cn(
                    'fixed left-1/2 z-50 w-full max-w-lg -translate-x-1/2 rounded-xl border border-border bg-card text-card-foreground shadow-md duration-200 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95',
                    centered ? 'top-1/2 -translate-y-1/2' : 'top-12',
                    sectioned ? 'flex max-h-[85dvh] flex-col overflow-hidden' : 'grid gap-4 p-6',
                    className,
                )}
                {...props}>
                <DialogSectionedContext value={!!sectioned}>{children}</DialogSectionedContext>
                <DialogPrimitive.Close className='absolute top-4 right-4 rounded-md p-1 text-muted-foreground opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:pointer-events-none'>
                    <span className='icon-[mdi--close] size-4' />
                    <span className='sr-only'>Close</span>
                </DialogPrimitive.Close>
            </DialogPrimitive.Content>
        </DialogPrimitive.Portal>
    );
}

// Groups the title + description. In a `sectioned` DialogContent it becomes a
// bordered title bar: fixed-height and vertically centred so the title lines up
// with the absolute close button (pe-12 keeps the title clear of it).
export function DialogHeader({ className, ...props }: ComponentProps<'div'>) {
    const sectioned = useDialogSectioned();
    return (
        <div
            className={cn(
                sectioned
                    ? 'flex min-h-16 flex-col justify-center gap-1 border-b border-border px-6 py-3 pe-12 text-left'
                    : 'flex flex-col gap-1.5 text-center sm:text-left',
                className,
            )}
            {...props}
        />
    );
}

// Accessible dialog heading (wires aria-labelledby).
export function DialogTitle({ className, ...props }: ComponentProps<typeof DialogPrimitive.Title>) {
    return (
        <DialogPrimitive.Title
            className={cn('text-lg leading-none font-semibold tracking-tight text-foreground', className)}
            {...props}
        />
    );
}

// Accessible dialog body text (wires aria-describedby).
export function DialogDescription({ className, ...props }: ComponentProps<typeof DialogPrimitive.Description>) {
    return <DialogPrimitive.Description className={cn('text-sm text-muted-foreground', className)} {...props} />;
}

// Scrollable content region between the header and footer. In a `sectioned`
// DialogContent it grows to fill the flex column and scrolls when the content
// exceeds the modal's max height, keeping the header + footer pinned.
export function DialogBody({ className, ...props }: ComponentProps<'div'>) {
    const sectioned = useDialogSectioned();
    return <div className={cn('flex-1 overflow-y-auto', sectioned && 'px-6 py-4', className)} {...props} />;
}

// Action row, right-aligned on desktop. In a `sectioned` DialogContent it becomes
// a bordered, tinted action bar.
export function DialogFooter({ className, ...props }: ComponentProps<'div'>) {
    const sectioned = useDialogSectioned();
    return (
        <div
            className={cn(
                sectioned
                    ? 'flex items-center justify-end gap-2 border-t border-border bg-muted/40 px-6 py-3'
                    : 'flex flex-col-reverse gap-2 sm:flex-row sm:justify-end',
                className,
            )}
            {...props}
        />
    );
}
