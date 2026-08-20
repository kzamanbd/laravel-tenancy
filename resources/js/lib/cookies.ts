/**
 * Cookie helpers — the React port's replacement for Nuxt's `useCookie`.
 *
 * Default is `SameSite=Lax` — the secure choice for a real deployment.
 *
 * When the app is embedded cross-origin (e.g. the ThemeForest **preview
 * iframe**, where the demo is a third-party frame), `Lax` cookies are not sent
 * and the theme/auth cookies would reset on every navigation. Set
 * `VITE_COOKIE_CROSS_SITE=true` when **building the demo** to switch these
 * cookies to `SameSite=None; Secure; Partitioned` (CHIPS) so they survive the
 * iframe. Leave it unset for buyer/production builds.
 */
const crossSite = import.meta.env.VITE_COOKIE_CROSS_SITE === 'true';

export function cookieSecurity(): string {
    return crossSite ? '; SameSite=None; Secure; Partitioned' : '; SameSite=Lax';
}

export function readCookie(name: string): string | null {
    if (typeof document === 'undefined') {
return null;
}

    const match = document.cookie.split('; ').find(row => row.startsWith(`${name}=`));

    return match ? decodeURIComponent(match.slice(name.length + 1)) : null;
}

export function writeCookie(name: string, value: string | null, maxAge = 60 * 60 * 24 * 365) {
    if (typeof document === 'undefined') {
return;
}

    if (value === null) {
        document.cookie = `${name}=; path=/; max-age=0${cookieSecurity()}`;

        return;
    }

    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}${cookieSecurity()}`;
}

/** Read a JSON cookie, falling back to `fallback` when absent or malformed. */
export function readJsonCookie<T>(name: string, fallback: T): T {
    const raw = readCookie(name);

    if (raw === null) {
return fallback;
}

    try {
        return JSON.parse(raw) as T;
    } catch {
        return fallback;
    }
}
