import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

export type ProgressProps = ComponentProps<'div'> & {
    value?: number;
    max?: number;
    indicatorClass?: string;
    label?: string;
};

// Determinate progress bar; colour the fill via `indicatorClass` (defaults to the
// primary token so it follows the theme). `label` sets the accessible name (a11y:
// progressbars need a name).
export function Progress({
    className,
    value = 0,
    max = 100,
    indicatorClass,
    label = 'Progress',
    ...props
}: ProgressProps) {
    const pct = Math.min(100, Math.max(0, (value / (max || 100)) * 100));

    return (
        <div
            role='progressbar'
            aria-label={label}
            aria-valuenow={value}
            aria-valuemin={0}
            aria-valuemax={max}
            aria-valuetext={`${Math.round(pct)}%`}
            className={cn('relative h-2 w-full overflow-hidden rounded-full bg-muted', className)}
            {...props}>
            <div
                className={cn('h-full rounded-full bg-primary transition-all duration-300', indicatorClass)}
                style={{ width: `${pct}%` }}
            />
        </div>
    );
}
