import type { ReactNode } from 'react';
import DefaultLayout from '@/layouts/default-layout';

/**
 * The authenticated shell. Kept as a thin alias so pages keep importing
 * `@/layouts/app-layout` while the dashboard chrome lives in DefaultLayout.
 */
export default function AppLayout({ children }: { children: ReactNode }) {
    return <DefaultLayout>{children}</DefaultLayout>;
}
