import { Link } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useLocation } from '@/hooks/use-router';
import type { MenuNode } from '@/lib/menu';
import { cn } from '@/lib/utils';

export default function HorizontalMenuNode({ node, level = 0 }: { node: MenuNode; level?: number }) {
    const { pathname } = useLocation();
    // Mark the active route's link for assistive tech (mirrors VerticalMenuNode).
    const linkActive = !!node.to && pathname === node.to;

    const isTop = level === 0;
    const hasChildren = !!node.children?.length;

    // Section headings are not shown in the horizontal bar
    if (node.heading) {
return null;
}

    const iconAndLabel = (
        <div className='flex items-center'>
            {node.icon && <span className={node.icon}></span>}
            <span className='px-1'>{node.label}</span>
        </div>
    );

    // ── Top level (rendered as <li> in the horizontal bar) ──
    if (isTop) {
        // Top-level dropdown
        if (hasChildren) {
            return (
                <li className='tw-menu-item'>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <button type='button' className='nav-link'>
                                {iconAndLabel}
                                <span className='tw-arrow'>
                                    <i className='icon-[mdi--chevron-down]'></i>
                                </span>
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align='start' className='min-w-48'>
                            {node.children?.map((child, i) => (
                                <HorizontalMenuNode key={i} node={child} level={level + 1} />
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </li>
            );
        }

        // Top-level single link
        return (
            <li className='tw-menu-item'>
                {node.to ? (
                    <Link
                        href={node.to}
                        target={node.target}
                        className={cn('nav-link', linkActive && 'active')}
                        aria-current={linkActive ? 'page' : undefined}>
                        {iconAndLabel}
                    </Link>
                ) : (
                    <a
                        href={node.href || '#'}
                        target={node.target}
                        rel={node.target === '_blank' ? 'noopener noreferrer' : undefined}
                        className='nav-link'>
                        {iconAndLabel}
                    </a>
                )}
            </li>
        );
    }

    // ── Nested submenu (renders inside a dropdown panel) ──
    if (hasChildren) {
        return (
            <DropdownMenuSub>
                <DropdownMenuSubTrigger className='twd--link justify-between'>
                    <span>{node.label}</span>
                </DropdownMenuSubTrigger>
                <DropdownMenuSubContent className='min-w-48'>
                    {node.children?.map((child, i) => (
                        <HorizontalMenuNode key={i} node={child} level={level + 1} />
                    ))}
                </DropdownMenuSubContent>
            </DropdownMenuSub>
        );
    }

    // ── Nested leaf (menu item inside a dropdown panel) ──
    return (
        <DropdownMenuItem asChild>
            {node.to ? (
                <Link
                    href={node.to}
                    target={node.target}
                    className={cn('twd--link', linkActive && 'active')}
                    aria-current={linkActive ? 'page' : undefined}>
                    {node.label}
                </Link>
            ) : (
                <a
                    href={node.href || '#'}
                    target={node.target}
                    rel={node.target === '_blank' ? 'noopener noreferrer' : undefined}
                    className='twd--link'>
                    {node.label}
                </a>
            )}
        </DropdownMenuItem>
    );
}
