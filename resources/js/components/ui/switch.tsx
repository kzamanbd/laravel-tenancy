import { cn } from '@/lib/utils';
import type { ComponentProps } from 'react';

export type SwitchProps = Omit<ComponentProps<'button'>, 'onChange'> & {
    checked?: boolean;
    onCheckedChange?: (checked: boolean) => void;
};

// Accessible toggle (role=switch). The thumb shifts on the inline axis so it
// tracks correctly under RTL.
export function Switch({ className, checked = false, onCheckedChange, disabled, ...props }: SwitchProps) {
    return (
        <button
            type='button'
            role='switch'
            aria-checked={checked}
            disabled={disabled}
            onClick={() => onCheckedChange?.(!checked)}
            className={cn(
                'relative inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                checked ? 'bg-primary' : 'bg-input',
                className,
            )}
            {...props}>
            <span
                className={cn(
                    'pointer-events-none inline-block size-4 transform rounded-full bg-card shadow transition-transform',
                    checked ? 'translate-x-4 rtl:-translate-x-4' : 'translate-x-0',
                )}
            />
        </button>
    );
}
