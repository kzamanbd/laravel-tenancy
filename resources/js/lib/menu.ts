// Single source of truth for the sidebar navigation. VerticalMenu, the
// horizontal menu, the global search, and the breadcrumb trail all render from
// this array rather than duplicating static markup.
//
// Iconify note: each full Iconify utility class must appear verbatim in source
// to be generated. Storing the complete class string here satisfies that — the
// classes are never built dynamically.

export interface MenuBadge {
    text: string;
    class?: string;
}

export interface MenuNode {
    /** Display text (also the search index). */
    label: string;

    /** Section divider — renders as a heading, not a link. */
    heading?: boolean;
    /** Literal Iconify class, e.g. 'icon-[mdi--view-dashboard-outline]'. Top level only. */
    icon?: string;
    /** Internal route (Inertia `Link`). */
    to?: string;
    /** Raw href (external / placeholder). */
    href?: string;
    /** Link target, e.g. '_blank'. */
    target?: string;
    badge?: MenuBadge;
    children?: MenuNode[];
}

/**
 * Navigation for a single status page.
 *
 * Workspace routes live on the tenant's own domain and take the tenant from the
 * host, so these are plain same-origin paths -- this menu only renders once a
 * tenant has been resolved, which means the request is already on the right
 * host.
 */
export const workspaceMenu = (): MenuNode[] => [
    { label: 'This page', heading: true },
    {
        label: 'Components',
        icon: 'icon-[mdi--view-grid-outline]',
        to: '/workspaces/components',
    },
    {
        label: 'Incidents',
        icon: 'icon-[mdi--alert-outline]',
        to: '/workspaces/incidents',
    },
    {
        label: 'Maintenance',
        icon: 'icon-[mdi--calendar-clock-outline]',
        to: '/workspaces/maintenance',
    },
    {
        label: 'Subscribers',
        icon: 'icon-[mdi--email-outline]',
        to: '/workspaces/subscribers',
    },
    { label: 'Domains', icon: 'icon-[mdi--web]', to: '/workspaces/domains' },
    {
        label: 'Page settings',
        icon: 'icon-[mdi--palette-outline]',
        to: '/workspaces/settings',
    },
];

export const menu: MenuNode[] = [
    { label: 'Platform', heading: true },
    {
        label: 'Dashboard',
        icon: 'icon-[mdi--view-dashboard-outline]',
        to: '/dashboard',
    },
    {
        label: 'Status Pages',
        icon: 'icon-[mdi--server-network-outline]',
        to: '/tenants',
    },

    { label: 'Account', heading: true },
    {
        label: 'Settings',
        icon: 'icon-[mdi--cog-outline]',
        children: [
            { label: 'Profile', to: '/settings/profile' },
            { label: 'Security', to: '/settings/security' },
        ],
    },
];

export interface FlatPage {
    label: string;
    to: string;
    icon: string;
    breadcrumb: string;
}

/**
 * Every linkable page in the tree, flattened for the global search index.
 * Headings and grouping nodes are skipped; children inherit their parent's icon.
 */
export function flattenMenu(nodes: MenuNode[] = menu): FlatPage[] {
    const out: FlatPage[] = [];

    const walk = (items: MenuNode[], trail: string[], icon: string) => {
        for (const node of items) {
            if (node.heading) {
                continue;
            }

            const nodeIcon = node.icon || icon;

            if (node.children?.length) {
                walk(node.children, [...trail, node.label], nodeIcon);
            } else if (node.to) {
                out.push({
                    label: node.label,
                    to: node.to,
                    icon: nodeIcon || 'icon-[mdi--file-outline]',
                    breadcrumb: trail.join(' › '),
                });
            }
        }
    };

    walk(nodes, [], '');

    return out;
}
