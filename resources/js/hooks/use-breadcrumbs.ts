import { useMemo } from 'react';
import { useLocation } from '@/hooks/use-router';
import { menu  } from '@/lib/menu';
import type {MenuNode} from '@/lib/menu';

export interface Crumb {
    title: string;
    to: string;
    disabled: boolean;
    link: boolean;
}

/**
 * Every path the sidebar actually links to.
 *
 * The template read this from vite-plugin-pages' generated route manifest.
 * Inertia resolves pages server-side, so the menu is the closest thing to a
 * route table on the client. Segments that only group children (and therefore
 * have no page of their own) stay plain text rather than dead links.
 */
const collectPaths = (nodes: MenuNode[]): string[] =>
    nodes.flatMap((node) => [
        ...(node.to ? [node.to.replace(/\/$/, '') || '/'] : []),
        ...collectPaths(node.children ?? []),
    ]);

const linkablePaths = new Set(collectPaths(menu));

export const useBreadcrumbs = () => {
    const { pathname } = useLocation();

    return useMemo(() => {
        const segments = pathname.split('/').filter((segment) => segment !== '');

        const crumbs: Crumb[] = segments.map((segment, index) => {
            const path = '/' + segments.slice(0, index + 1).join('/');
            const isLast = index === segments.length - 1;

            const title = segment
                .replace(/[-_]/g, ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());

            return {
                title,
                to: path,
                disabled: isLast,
                link: !isLast && linkablePaths.has(path),
            };
        });

        const homeCrumb: Crumb = { title: 'Home', to: '/', disabled: true, link: false };

        return crumbs.length > 0 ? crumbs : [homeCrumb];
    }, [pathname]);
};
