import { router, usePage } from '@inertiajs/react';

/**
 * react-router compatibility shims.
 *
 * The dashboard template was written against react-router. Inertia owns routing
 * here, so rather than rewriting every ported component's control flow, these
 * expose the two hooks the template relies on, backed by Inertia's page state.
 */

type Location = {
    pathname: string;
    search: string;
    hash: string;
};

/**
 * Inertia exposes the current page as a single URL string. Split it into the
 * shape the template's active-link checks expect.
 */
export function useLocation(): Location {
    const { url } = usePage();

    const [pathAndQuery = '', hash = ''] = url.split('#');
    const [pathname = '/', search = ''] = pathAndQuery.split('?');

    return {
        pathname: pathname || '/',
        search: search ? `?${search}` : '',
        hash: hash ? `#${hash}` : '',
    };
}

/**
 * A negative number walks browser history, matching react-router's behaviour
 * for `navigate(-1)`; anything else is an Inertia visit.
 */
export function useNavigate() {
    return (to: string | number): void => {
        if (typeof to === 'number') {
            window.history.go(to);

            return;
        }

        router.visit(to);
    };
}
