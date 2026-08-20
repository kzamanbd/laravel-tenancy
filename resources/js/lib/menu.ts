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
 * Navigation for a single status page. These routes only exist once a tenant is
 * resolved from the path, so they are appended to the base menu rather than
 * living in it.
 */
export const workspaceMenu = (tenantId: number): MenuNode[] => [
    { label: 'This page', heading: true },
    {
        label: 'Components',
        icon: 'icon-[mdi--view-grid-outline]',
        to: `/workspaces/${tenantId}/components`,
    },
    {
        label: 'Incidents',
        icon: 'icon-[mdi--alert-outline]',
        to: `/workspaces/${tenantId}/incidents`,
    },
    {
        label: 'Maintenance',
        icon: 'icon-[mdi--calendar-clock-outline]',
        to: `/workspaces/${tenantId}/maintenance`,
    },
    {
        label: 'Page settings',
        icon: 'icon-[mdi--palette-outline]',
        to: `/workspaces/${tenantId}/settings`,
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
