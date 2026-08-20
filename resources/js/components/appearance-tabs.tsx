import type { HTMLAttributes } from 'react';
import { useTheme  } from '@/context/theme-context';
import type {Settings} from '@/context/theme-context';
import { cn } from '@/lib/utils';

/**
 * Light / dark / system switch, driving the design system's ThemeProvider so
 * the choice persists in the same cookie the rest of the chrome reads.
 */
export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    const { settings, update } = useTheme();

    const tabs: { value: Settings['theme']; icon: string; label: string }[] = [
        { value: 'light', icon: 'icon-[mdi--white-balance-sunny]', label: 'Light' },
        { value: 'dark', icon: 'icon-[mdi--moon-waning-crescent]', label: 'Dark' },
        { value: 'system', icon: 'icon-[mdi--monitor]', label: 'System' },
    ];

    return (
        <div
            className={cn('inline-flex gap-1 rounded-lg bg-muted p-1', className)}
            {...props}
        >
            {tabs.map(({ value, icon, label }) => (
                <button
                    key={value}
                    type="button"
                    aria-pressed={settings.theme === value}
                    onClick={() => update({ theme: value })}
                    className={cn(
                        'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                        settings.theme === value
                            ? 'bg-card text-foreground shadow-xs'
                            : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                    )}
                >
                    <span className={cn('-ml-1 size-4', icon)} />
                    <span className="ml-1.5 text-sm">{label}</span>
                </button>
            ))}
        </div>
    );
}
