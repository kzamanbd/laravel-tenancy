import { useEffect, useState } from 'react';
import { useTheme } from '@/context/theme-context';

/**
 * The theme actually in effect, with `system` resolved against the OS setting.
 *
 * Components that need a concrete light/dark value — rendering a QR code, or
 * anything else that cannot express itself in CSS tokens — use this rather than
 * reading `settings.theme`, which may be `system`.
 */
export function useResolvedAppearance(): 'light' | 'dark' {
    const { settings } = useTheme();

    const [prefersDark, setPrefersDark] = useState(
        () => typeof window !== 'undefined'
            && window.matchMedia('(prefers-color-scheme: dark)').matches,
    );

    useEffect(() => {
        const media = window.matchMedia('(prefers-color-scheme: dark)');
        const handler = (event: MediaQueryListEvent) => setPrefersDark(event.matches);

        media.addEventListener('change', handler);

        return () => media.removeEventListener('change', handler);
    }, []);

    if (settings.theme === 'system') {
        return prefersDark ? 'dark' : 'light';
    }

    return settings.theme;
}
