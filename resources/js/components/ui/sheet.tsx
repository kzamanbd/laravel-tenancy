import { DialogSectionedContext, useDialogSectioned } from '@/components/ui/dialog-sectioned';
import { cn } from '@/lib/utils';
import { cva, type VariantProps } from 'class-variance-authority';
import * as DialogPrimitive from '@radix-ui/react-dialog';
import type { ComponentProps } from 'react';

// shadcn-style Sheet root (a Dialog that slides in from an edge).
export const Sheet = DialogPrimitive.Root;
export const SheetTrigger = DialogPrimitive.Trigger;
export const SheetClose = DialogPrimitive.Close;
export const SheetTitle = DialogPrimitive.Title;
export const SheetDescription = DialogPrimitive.Description;

// A DialogContent that slides in from an edge; left/right are full-height,
// top/bottom full-width.
const sheetVariants = cva(
    'fixed z-50 flex flex-col gap-4 border-border bg-card p-6 text-card-foreground shadow-md transition ease-in-out data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=open]:duration-300 data-[state=closed]:duration-200',
    {
        variants: {
            side: {
                top: 'inset-x-0 top-0 border-b data-[state=open]:slide-in-from-top data-[state=closed]:slide-out-to-top',
                bottom: 'inset-x-0 bottom-0 border-t data-[state=open]:slide-in-from-bottom data-[state=closed]:slide-out-to-bottom',
                left: 'inset-y-0 left-0 h-full w-3/4 border-r data-[state=open]:slide-in-from-left data-[state=closed]:slide-out-to-left sm:max-w-sm',
                right: 'inset-y-0 right-0 h-full w-3/4 border-l data-[state=open]:slide-in-from-right data-[state=closed]:slide-out-to-right sm:max-w-sm',
            },
        },
        defaultVariants: { side: 'right' },
    },
);

export type SheetContentProps = ComponentProps<typeof DialogPrimitive.Content> &
    VariantProps<typeof sheetVariants> & {
        // `sectioned` mirrors DialogContent: the drawer drops its padding/gap and
        // SheetHeader / SheetBody / SheetFooter pick up their dividers + spacing.
        sectioned?: boolean;
    };

export function SheetContent({ className, children, side = 'right', sectioned, ...props }: SheetContentProps) {
    return (
        <DialogPrimitive.Portal>
            <DialogPrimitive.Overlay className='fixed inset-0 z-50 bg-black/60 backdrop-blur-sm data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:animate-in data-[state=open]:fade-in-0' />
            <DialogPrimitive.Content
                className={cn(sheetVariants({ side }), sectioned && 'gap-0 overflow-hidden p-0', className)}
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

// Groups the drawer title + description. In a `sectioned` SheetContent it becomes
// a bordered title bar with the close-button clearance.
export function SheetHeader({ className, ...props }: ComponentProps<'div'>) {
    const sectioned = useDialogSectioned();
    return (
        <div
            className={cn(
                sectioned
                    ? 'flex min-h-16 flex-col justify-center gap-1 border-b border-border px-5 py-3 pe-12 text-left'
                    : 'flex flex-col gap-1.5 p-6 text-center sm:text-left',
                className,
            )}
            {...props}
        />
    );
}

// Scrollable drawer content between header and footer; grows to fill the column
// so the footer stays pinned to the bottom edge.
export function SheetBody({ className, ...props }: ComponentProps<'div'>) {
    const sectioned = useDialogSectioned();
    return <div className={cn('flex-1 overflow-y-auto', sectioned && 'px-5 py-4', className)} {...props} />;
}

// Drawer action row. In a `sectioned` SheetContent it becomes a bordered, tinted
// action bar.
export function SheetFooter({ className, ...props }: ComponentProps<'div'>) {
    const sectioned = useDialogSectioned();
    return (
        <div
            className={cn(
                sectioned
                    ? 'flex items-center justify-end gap-2 border-t border-border bg-muted/40 px-5 py-3'
                    : 'flex flex-col-reverse gap-2 sm:flex-row sm:justify-end',
                className,
            )}
            {...props}
        />
    );
}
