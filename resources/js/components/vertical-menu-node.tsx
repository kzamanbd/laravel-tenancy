import { Link } from '@inertiajs/react';
import { createContext, use } from 'react';
import { Badge } from '@/components/ui/badge';
import { useLocation } from '@/hooks/use-router';
import type { MenuNode } from '@/lib/menu';
import { cn } from '@/lib/utils';

// Open-group state shared from VerticalMenu (keyed by label trail). A pure-CSS
// accordion; visibility/arrow/highlight are driven by the `active` class on the
// <li> (see .twd--menu CSS), open-state stays in React.
export const VerticalMenuContext = createContext<{
    openGroups: string[];
    toggleGroup: (key: string) => void;
} | null>(null);

export default function VerticalMenuNode({
    node,
    level = 0,
    parentKey,
}: {
    node: MenuNode;
    level?: number;
    parentKey?: string;
}) {
    const { pathname } = useLocation();
    const vmenu = use(VerticalMenuContext);

    const isTop = level === 0;
    const hasChildren = !!node.children?.length;

    // Globally-unique key from the label trail (e.g. 'Pages/ECommerce').
    const nodeKey = parentKey ? `${parentKey}/${node.label}` : node.label;
    const isOpen = !!vmenu?.openGroups.includes(nodeKey);

    // Active-route detection from the router (no DOM querying).
    const linkActive = !!node.to && pathname === node.to;

    // Section heading
    if (node.heading) {
        return (
            <li className='tw-menu-header'>
                <span className='minus-icon'>
                    <i className='icon-[mdi--minus]'></i>
                </span>
                <span className='minus-label'>{node.label}</span>
            </li>
        );
    }

    const topLabel = (
        <>
            {isTop && node.icon && <span className={node.icon}></span>}
            {isTop ? (
                <span className='tw-link-label'>
                    {node.label}
                    {node.badge && (
                        <Badge variant='warning' size='sm' pill className={node.badge.class}>
                            {node.badge.text}
                        </Badge>
                    )}
                </span>
            ) : (
                node.label
            )}
        </>
    );

    // Collapsible group (has children)
    if (hasChildren) {
        return (
            <li className={cn(isTop ? 'tw-menu-item' : 'twd--menu-item', isOpen && 'active')}>
                <button
                    type='button'
                    className={isTop ? 'tw-menu-link' : 'twd--link'}
                    aria-expanded={isOpen}
                    onClick={() => vmenu?.toggleGroup(nodeKey)}>
                    {topLabel}
                    <span className='tw-arrow'>
                        <i className='icon-[mdi--chevron-down]'></i>
                    </span>
                </button>

                {/* grid-rows 0fr→1fr wrapper animates the accordion open/close height */}
                <div className='twd--submenu'>
                    <ul className='twd--menu'>
                        {node.children?.map((child, i) => (
                            <VerticalMenuNode key={i} node={child} level={level + 1} parentKey={nodeKey} />
                        ))}
                    </ul>
                </div>
            </li>
        );
    }

    // Leaf: internal route
    if (node.to) {
        return (
            <li className={isTop ? 'tw-menu-item' : 'twd--menu-item'}>
                <Link
                    href={node.to}
                    target={node.target}
                    className={cn(isTop ? 'tw-menu-link' : 'twd--link', linkActive && 'active')}
                    aria-current={linkActive ? 'page' : undefined}>
                    {topLabel}
                </Link>
            </li>
        );
    }

    // Leaf: raw href / placeholder
    return (
        <li className={isTop ? 'tw-menu-item' : 'twd--menu-item'}>
            <a
                href={node.href || '#'}
                target={node.target}
                rel={node.target === '_blank' ? 'noopener noreferrer' : undefined}
                className={isTop ? 'tw-menu-link' : 'twd--link'}>
                {isTop && node.icon && <span className={node.icon}></span>}
                {isTop ? <span className='tw-link-label'>{node.label}</span> : node.label}
            </a>
        </li>
    );
}
