import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { NotificationsProvider } from '@/context/notifications-context';
import { ThemeProvider } from '@/context/theme-context';
import { UiProvider } from '@/context/ui-context';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import SettingsLayout from '@/layouts/settings/layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        // UiProvider first: ThemeProvider reads the mobile drawer state from it,
        // and the dashboard chrome reads both.
        return (
            <UiProvider>
                <ThemeProvider>
                    <NotificationsProvider>
                        <TooltipProvider delayDuration={200}>
                            {app}
                            <Toaster position="top-right" richColors />
                        </TooltipProvider>
                    </NotificationsProvider>
                </ThemeProvider>
            </UiProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});
