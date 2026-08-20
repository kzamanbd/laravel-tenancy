import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLogo from '@/components/app-logo';
import { home } from '@/routes';

/**
 * The unauthenticated shell: a centred card floating on the animated radial
 * gradient, with decorative squares bleeding past its corners.
 *
 * Pages supply `title`/`description` through their static `layout` object.
 */
export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: ReactNode;
}) {
    return (
        <>
            <a href="#main-content" className="skip-to-content">
                Skip to main content
            </a>

            <div className="tw--radial-gradient">
                <main
                    id="main-content"
                    tabIndex={-1}
                    className="flex min-h-screen items-center justify-center overflow-hidden p-4 md:p-6"
                >
                    <div className="auth-card">
                        <div className="absolute -top-10 -left-10 z-[-1] size-60 rounded-3xl bg-primary-300/10 before:absolute before:-top-10 before:-right-10 before:z-[-1] before:size-36 before:rounded-3xl before:border-2 before:border-primary-500/10" />
                        <div className="absolute -right-10 -bottom-10 z-[-1] size-44 rounded-3xl bg-primary-300/10 before:absolute before:-right-6 before:-bottom-6 before:z-[-1] before:size-60 before:border-2 before:border-r-0 before:border-b-0 before:border-dashed before:border-primary-500/10" />

                        <div className="card-body">
                            <div className="my-4 flex items-center justify-center">
                                <Link href={home()} aria-label="Home">
                                    <AppLogo className="tw-logo-icon" />
                                </Link>
                            </div>

                            <div className="my-3 space-y-2 text-center">
                                <h1 className="text-2xl font-semibold">{title}</h1>
                                {description && <p className="text-xs text-muted-foreground">{description}</p>}
                            </div>

                            {children}
                        </div>
                    </div>
                </main>
            </div>
        </>
    );
}
