import { Link } from '@inertiajs/react';
import { useMemo, useRef, useState } from 'react';
import EmptyState from '@/components/empty-state';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { useUi } from '@/context/ui-context';
import { useNavigate } from '@/hooks/use-router';
import { flattenMenu } from '@/lib/menu';

// Search index built from the same menu data the sidebar renders from.
const pages = flattenMenu();

const QUICK = ['/dashboard', '/dashboard/analytics', '/apps/chats', '/apps/email', '/apps/tasks', '/apps/calendars'];
const quickLinks = QUICK.map(to => pages.find(p => p.to === to)).filter((p): p is NonNullable<typeof p> => !!p);

export default function GlobalSearch() {
    const navigate = useNavigate();
    const { searchOpen, setSearchOpen } = useUi();
    const [query, setQuery] = useState('');
    const searchInput = useRef<HTMLInputElement>(null);

    const results = useMemo(() => {
        const q = query.trim().toLowerCase();

        if (!q) {
return [];
}

        return pages
            .filter(p => p.label.toLowerCase().includes(q) || p.breadcrumb.toLowerCase().includes(q))
            .slice(0, 12);
    }, [query]);

    // Clear the query and close the dialog after a result is chosen.
    const onSelect = () => {
        setQuery('');
        setSearchOpen(false);
    };

    // Enter jumps to the first result: navigate, then close the overlay.
    const onEnter = () => {
        const first = results[0];

        if (!first) {
return;
}

        navigate(first.to);
        onSelect();
    };

    return (
        <Dialog open={searchOpen} onOpenChange={setSearchOpen}>
            <DialogContent
                className='max-w-xl gap-0 p-0 lg:my-24'
                aria-label='Search'
                aria-describedby={undefined}
                // Keep the input focused when the dialog opens (overrides Radix's
                // default focus target so typing starts immediately).
                onOpenAutoFocus={event => {
                    event.preventDefault();
                    requestAnimationFrame(() => searchInput.current?.focus());
                }}>
                <header className='flex items-center gap-3 border-b border-border py-3 ps-4 pe-12'>
                    <div className='icon-[mdi--search] text-muted-foreground' aria-hidden='true'></div>
                    <input
                        id='s-box-input'
                        ref={searchInput}
                        value={query}
                        onChange={e => setQuery(e.target.value)}
                        type='text'
                        className='form-control w-full text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none!'
                        aria-label='Search'
                        placeholder='Search pages...'
                        onKeyDown={e => e.key === 'Enter' && onEnter()}
                    />
                </header>
                <main className='flex max-h-125 min-h-44 flex-col gap-4 overflow-y-auto px-1 py-2'>
                    {query.trim() ? (
                        /* Search results */
                        <div className='px-1'>
                            <p className='mb-2 px-2 text-xs text-muted-foreground' role='status' aria-live='polite'>
                                Results: {results.length}
                            </p>
                            {results.map(page => (
                                <Link
                                    key={page.to}
                                    href={page.to}
                                    className='flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-accent'
                                    onClick={onSelect}>
                                    <span className={`${page.icon} size-5 shrink-0 text-muted-foreground`}></span>
                                    <span className='text-sm dark:text-foreground'>{page.label}</span>
                                    {page.breadcrumb && (
                                        <span className='ms-auto text-xs text-muted-foreground'>{page.breadcrumb}</span>
                                    )}
                                </Link>
                            ))}
                            {!results.length && (
                                <EmptyState icon='icon-[mdi--magnify-close]' title={`No pages found for “${query}”.`} />
                            )}
                        </div>
                    ) : (
                        /* Quick links (shown when the search box is empty) */
                        <div className='px-1'>
                            <p className='mb-2 px-2 text-xs text-muted-foreground'>Quick links</p>
                            {quickLinks.map(page => (
                                <Link
                                    key={page.to}
                                    href={page.to}
                                    className='flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-accent'
                                    onClick={onSelect}>
                                    <span className={`${page.icon} size-5 shrink-0 text-muted-foreground`}></span>
                                    <span className='text-sm dark:text-foreground'>{page.label}</span>
                                    {page.breadcrumb && (
                                        <span className='ms-auto text-xs text-muted-foreground'>{page.breadcrumb}</span>
                                    )}
                                </Link>
                            ))}
                        </div>
                    )}
                </main>
                <footer className='border-t border-border px-4 py-2'>
                    <div className='flex w-full items-center justify-between text-xs text-muted-foreground'>
                        <span>
                            <kbd className='rounded-sm border p-0.5 dark:text-foreground'>ESC</kbd> to close
                        </span>
                        <span>
                            <kbd className='rounded-sm border p-0.5 dark:text-foreground'>↵</kbd> open first result
                        </span>
                    </div>
                </footer>
            </DialogContent>
        </Dialog>
    );
}
