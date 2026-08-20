import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import GlobalSearch from '@/components/global-search';
import NavbarNav from '@/components/navbar-nav';
import SystemActivity from '@/components/system-activity';
import TweaksPanel from '@/components/tweaks-panel';
import VerticalMenu from '@/components/vertical-menu';
import { useTheme } from '@/context/theme-context';
import { useUi } from '@/context/ui-context';
import { useBreadcrumbs } from '@/hooks/use-breadcrumbs';

export default function DefaultLayout({ children }: { children: ReactNode }) {
    const { wrapperClass, footerClass } = useTheme();
    const appName = usePage<{ name: string }>().props.name;
    const { isMobileMenuOpen } = useUi();

    // A single page-level <h1> (WCAG 2.4.6 / page-has-heading-one). Pages render
    // their own section headings (h2+); this names the page for AT from the
    // current breadcrumb so every route has exactly one top-level heading.
    const breadcrumbs = useBreadcrumbs();
    const pageHeading = breadcrumbs[breadcrumbs.length - 1]?.title ?? 'Dashboard';

    return (
        <div className={wrapperClass}>
            {/* Bypass blocks: first focusable element jumps keyboard/SR users past the chrome */}
            <a href='#main-content' className='skip-to-content'>
                Skip to main content
            </a>
            <VerticalMenu />
            {/* start main content — inert while the mobile drawer is open so focus/AT stay trapped in the drawer */}
            <div
                inert={isMobileMenuOpen}
                className='relative flex min-h-screen flex-col print:m-0 vertical:transition-[margin] vertical:duration-300 vertical:ease-in-out vertical:ltr:lg:ml-64 vertical:rtl:lg:mr-64 collapsed-menu:ltr:lg:ml-17.5 collapsed-menu:rtl:lg:mr-17.5'>
                <NavbarNav />
                <main
                    id='main-content'
                    tabIndex={-1}
                    className='animate__animated relative mx-3 mb-3 flex flex-1 flex-col pt-4 sm:mx-6 dark:text-foreground print:m-0 print:p-0'>
                    <h1 className='sr-only'>{pageHeading}</h1>
                    {children}
                </main>

                <footer className={footerClass}>
                    <span className='text-sm text-muted-foreground'>
                        &copy; {new Date().getFullYear()} {appName}
                    </span>
                </footer>
            </div>

            {/* Start search box */}
            <GlobalSearch />
            {/* Activity */}
            <SystemActivity />
            {/* Theme customizer */}
            <TweaksPanel />
        </div>
    );
}
