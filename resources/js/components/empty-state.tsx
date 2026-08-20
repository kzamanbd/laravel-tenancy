import type { ReactNode } from 'react';

// Reusable empty / no-results / error placeholder.
// Pass a full literal Iconify class (e.g. icon-[mdi--inbox-outline]) so the
// class is scanned in the caller's source. Use `children` for a CTA.
export default function EmptyState({
    icon = 'icon-[mdi--inbox-outline]',
    title,
    description = '',
    children,
}: {
    icon?: string;
    title: string;
    description?: string;
    children?: ReactNode;
}) {
    return (
        <div className='flex flex-col items-center justify-center gap-3 px-6 py-12 text-center'>
            <span className={`${icon} size-10 text-muted-foreground`} aria-hidden='true'></span>
            <div className='space-y-1'>
                <p className='text-sm font-medium text-foreground'>{title}</p>
                {description && <p className='mx-auto max-w-sm text-sm text-muted-foreground'>{description}</p>}
            </div>
            {children && <div className='mt-1'>{children}</div>}
        </div>
    );
}
