import { Link, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import AppLogo from '@/components/app-logo';
import { GripIcon } from '@/components/brand-icons';
import Simplebar from '@/components/simplebar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import VerticalMenuNode, {
    VerticalMenuContext,
} from '@/components/vertical-menu-node';
import { useAuth } from '@/context/auth-context';
import { useTheme } from '@/context/theme-context';
import { useUi } from '@/context/ui-context';
import { useFocusTrap } from '@/hooks/use-focus-trap';
import { useLocation } from '@/hooks/use-router';
import { menu, workspaceMenu } from '@/lib/menu';
import type { MenuNode } from '@/lib/menu';
import { cn } from '@/lib/utils';
import { index as tenantsRoute } from '@/routes/tenants';

// Ancestor group keys of `key` (its label-trail prefixes); e.g.
// 'Pages/ECommerce' -> ['Pages'].
const ancestorsOf = (key: string) => {
    return key
        .split('/')
        .slice(0, -1)
        .map((_, i, parts) => parts.slice(0, i + 1).join('/'));
};

// Collect the group keys (label trails) whose subtree contains `path`.
const activeGroupKeys = (
    nodes: MenuNode[],
    path: string,
    trail: string[] = [],
): string[] => {
    const keys: string[] = [];

    for (const n of nodes) {
        if (!n.children?.length) {
            continue;
        }

        const key = [...trail, n.label].join('/');
        const childKeys = activeGroupKeys(n.children, path, [
            ...trail,
            n.label,
        ]);

        if (childKeys.length || n.children.some((c) => c.to === path)) {
            keys.push(key, ...childKeys);
        }
    }

    return keys;
};

export default function VerticalMenu() {
    const { verticalMenuClass, toggleSidebar } = useTheme();
    const { isMobileMenuOpen, setMobileMenuOpen, toggleMobileMenu } = useUi();
    const { logout, email, tenants } = useAuth();
    const { props } = usePage<{
        name: string;
        tenant: { id: number } | null;
    }>();
    const appName = props.name;
    const activePage =
        tenants.find((page) => page.id === props.tenant?.id) ?? null;
    const { pathname } = useLocation();

    // Mobile slide-in drawer is a bespoke overlay (not a Radix dialog), so trap focus
    // inside it while open and restore focus on close. Esc closes it. The background
    // is marked `inert` in the layout so AT skips it too.
    const asideEl = useRef<HTMLElement | null>(null);
    useFocusTrap(asideEl, isMobileMenuOpen, () => setMobileMenuOpen(false));

    // Open accordion groups (label-trail keys), shared with VerticalMenuNode.
    // Pure-CSS accordion; visibility is CSS-driven off `.active`.
    // Status-page routes only exist while a tenant is resolved, so that section
    // appears and disappears with the workspace the request landed in.
    const nodes = useMemo(
        () => (props.tenant ? [...menu, ...workspaceMenu()] : menu),
        [props.tenant],
    );

    const [openGroups, setOpenGroups] = useState<string[]>(() =>
        activeGroupKeys(nodes, pathname),
    );

    // Accordion behaviour: only one group open per level. Opening a group keeps
    // its ancestor chain open but collapses its siblings (and their subtrees);
    // toggling an already-open group collapses just that branch.
    const toggleGroup = useCallback((key: string) => {
        setOpenGroups((prev) =>
            prev.includes(key) ? ancestorsOf(key) : [...ancestorsOf(key), key],
        );
    }, []);

    // Auto-open the active route's ancestor path. Replacing (not merging) keeps
    // the accordion single-open; on routes with no group, leave the current one open.
    // Also close the mobile slide-in drawer on navigation (tapping a link shouldn't
    // leave it open over the new page).
    // Adjusting state during render (rather than in an effect) is React's
    // prescribed pattern for deriving state from a changed input: it avoids the
    // extra render pass an effect would cause, and the flash of a stale
    // accordion on navigation.
    const [lastPathname, setLastPathname] = useState(pathname);

    if (pathname !== lastPathname) {
        setLastPathname(pathname);

        const active = activeGroupKeys(nodes, pathname);

        // Routes outside any group leave the current branch open.
        if (active.length) {
            setOpenGroups(active);
        }
    }

    // Navigating away closes the mobile drawer, so tapping a link does not
    // leave it hanging over the new page.
    useEffect(() => {
        setMobileMenuOpen(false);
    }, [pathname, setMobileMenuOpen]);

    const vmenu = useMemo(
        () => ({ openGroups, toggleGroup }),
        [openGroups, toggleGroup],
    );

    return (
        <VerticalMenuContext value={vmenu}>
            {/* menu shadow */}
            <div
                className={cn('menu-shadow', !isMobileMenuOpen && 'hidden')}
                onClick={toggleMobileMenu}
            ></div>

            {/* start vertical-menu */}
            <aside ref={asideEl} className={verticalMenuClass}>
                <div className="vertical-content">
                    {/* Menu Logo */}
                    <div className="tw-brand-logo">
                        <Link
                            href="/dashboard"
                            className="group inline-flex items-center gap-2"
                        >
                            <div className="tw-logo-container">
                                <AppLogo className="tw-logo-icon collapsed-menu:group-hover:hidden" />
                                <button
                                    type="button"
                                    className="hidden collapsed-menu:group-hover:block"
                                    onClick={(e) => {
                                        e.stopPropagation();
                                        e.preventDefault();
                                        toggleSidebar();
                                    }}
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="size-5"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            fill="none"
                                            stroke="currentColor"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth="1.5"
                                            d="M9 3.5v17M3 9.4c0-2.24 0-3.36.436-4.216a4 4 0 0 1 1.748-1.748C6.04 3 7.16 3 9.4 3h5.2c2.24 0 3.36 0 4.216.436a4 4 0 0 1 1.748 1.748C21 6.04 21 7.16 21 9.4v5.2c0 2.24 0 3.36-.436 4.216a4 4 0 0 1-1.748 1.748C17.96 21 16.84 21 14.6 21H9.4c-2.24 0-3.36 0-4.216-.436a4 4 0 0 1-1.748-1.748C3 17.96 3 16.84 3 14.6z"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div className="app-name">
                                <span>{appName}</span>
                            </div>
                        </Link>
                        <a
                            href="/"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Visit site"
                            className="ms-auto inline-flex size-7 items-center justify-center rounded-md text-muted-foreground hover:text-primary dark:text-muted-foreground semi-dark:text-muted-foreground collapsed-menu:hidden"
                        >
                            <span className="icon-[mdi--open-in-new] rtl:rotate-180"></span>
                        </a>
                        {/* mobile-only: close the slide-in drawer (desktop collapse lives in the navbar) */}
                        <button
                            type="button"
                            className="mini-sidebar lg:hidden"
                            aria-label="Toggle sidebar"
                            onClick={toggleMobileMenu}
                        >
                            <span className="icon-[mdi--close] size-5"></span>
                        </button>
                    </div>

                    {/* Menu Content (rendered from src/lib/menu.ts) */}
                    <Simplebar className="min-h-0 flex-1">
                        <ul className="tw-nav-menu">
                            {nodes.map((node, i) => (
                                <VerticalMenuNode
                                    key={i}
                                    node={node}
                                    level={0}
                                />
                            ))}
                        </ul>
                    </Simplebar>

                    {/* status page switcher */}
                    <div className="footer-menu-dropdown">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <button
                                    type="button"
                                    className="footer-menu-icon group"
                                >
                                    <span className="icon-[mdi--server-network-outline] size-5 shrink-0"></span>
                                    <span className="app-switcher-label">
                                        <div className="min-w-0">
                                            <span className="block truncate text-sm font-bold dark:text-foreground">
                                                {activePage?.name ??
                                                    'All status pages'}
                                            </span>
                                            <span className="block truncate text-xs dark:text-muted-foreground">
                                                {activePage?.domain ??
                                                    `${tenants.length} page${tenants.length === 1 ? '' : 's'}`}
                                            </span>
                                        </div>
                                        <svg
                                            className="ms-auto size-4 shrink-0"
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="24"
                                            height="24"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            strokeWidth="2"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        >
                                            <path d="m7 15 5 5 5-5"></path>
                                            <path d="m7 9 5-5 5 5"></path>
                                        </svg>
                                    </span>
                                </button>
                            </DropdownMenuTrigger>

                            <DropdownMenuContent
                                align="end"
                                side="top"
                                className="w-60"
                            >
                                {tenants.length === 0 ? (
                                    <div className="px-3 py-2 text-xs text-muted-foreground">
                                        No status pages yet.
                                    </div>
                                ) : (
                                    tenants.map((page) => (
                                        <a
                                            key={page.id}
                                            href={
                                                page.domain
                                                    ? `//${page.domain}`
                                                    : '#'
                                            }
                                            className="tw-nav-footer-item group"
                                        >
                                            <GripIcon />
                                            <span className="icon-[mdi--server-network-outline] size-4 shrink-0"></span>
                                            <span className="ms-2 block min-w-0">
                                                <span className="footer-menu-text truncate">
                                                    {page.name}
                                                </span>
                                                <span className="block truncate text-xs dark:text-muted-foreground">
                                                    {page.domain ?? page.slug}
                                                </span>
                                            </span>
                                        </a>
                                    ))
                                )}
                                <div className="item-group-divider"></div>
                                <Link
                                    href={tenantsRoute()}
                                    className="tw-nav-footer-item group"
                                >
                                    <span className="icon-[mdi--plus-circle-outline] size-4 shrink-0"></span>
                                    Add another status page
                                </Link>
                                <div className="item-group-divider"></div>
                                <button
                                    className="tw-nav-footer-item group flex justify-between"
                                    onClick={logout}
                                >
                                    <span>Sign out</span>
                                    <span className="truncate text-xs">
                                        {email}
                                    </span>
                                </button>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </aside>
            {/* end vertical-menu */}
        </VerticalMenuContext>
    );
}
