import { router, usePage } from '@inertiajs/react';
import { useCallback, useMemo } from 'react';
import { logout as logoutRoute } from '@/routes';

export interface AuthTenant {
    id: number;
    name: string | null;
    slug: string | null;
    domain: string | null;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
}

/**
 * The dashboard template shipped with a cookie-based auth stub. Laravel owns
 * sessions here, so the user comes from Inertia's shared props and signing out
 * posts to Fortify's logout route.
 *
 * Kept as a hook with the template's original shape so the ported components
 * did not need rewriting.
 */
export function useAuth() {
    const page = usePage<{ auth: { user: AuthUser | null; tenants: AuthTenant[] } }>();
    const user = page.props.auth?.user ?? null;
    const sharedTenants = page.props.auth?.tenants;

    const logout = useCallback(() => {
        router.post(logoutRoute().url);
    }, []);

    return useMemo(
        () => ({
            user,
            tenants: sharedTenants ?? [],
            email: user?.email ?? null,
            isAuthenticated: user !== null,
            logout,
        }),
        [user, sharedTenants, logout],
    );
}
