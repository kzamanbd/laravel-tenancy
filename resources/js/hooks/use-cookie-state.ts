import { useCallback, useState } from 'react';
import { readJsonCookie, writeCookie } from '@/lib/cookies';

/**
 * `useState` whose value is mirrored into a JSON cookie — the React equivalent
 * of Nuxt's `useCookie<T>()`. The initial read happens lazily so the value is
 * already correct on first paint (the no-flash script in `index.html` has
 * applied the same cookie to `<html>` by then).
 */
export function useCookieState<T>(name: string, fallback: () => T, maxAge?: number) {
    const [value, setValue] = useState<T>(() => readJsonCookie<T>(name, fallback()));

    const set = useCallback(
        (next: T | ((prev: T) => T)) => {
            setValue(prev => {
                const resolved = typeof next === 'function' ? (next as (p: T) => T)(prev) : next;
                writeCookie(name, resolved === null ? null : JSON.stringify(resolved), maxAge);

                return resolved;
            });
        },
        [name, maxAge],
    );

    return [value, set] as const;
}
