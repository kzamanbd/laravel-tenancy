import type { ReactNode } from 'react';

export default function GuestLayout({ children }: { children: ReactNode }) {
    return (
        <>
            <a href='#main-content' className='skip-to-content'>
                Skip to main content
            </a>
            <main id='main-content' tabIndex={-1}>
                {children}
            </main>
        </>
    );
}
