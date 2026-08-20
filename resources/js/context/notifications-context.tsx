import { createContext, use, useCallback, useMemo, useState  } from 'react';
import type {ReactNode} from 'react';

export interface AppNotification {
    id: number;
    title: string;
    read: boolean;
}

interface NotificationsContextValue {
    items: AppNotification[];
    unreadCount: number;
    add: (notification: AppNotification) => void;
    markAllRead: () => void;
    clear: () => void;
}

const NotificationsContext = createContext<NotificationsContextValue | null>(null);

// Example domain store: notification list + derived unread count. Demonstrates
// state + getters + actions a buyer would wire to their own API.
export function NotificationsProvider({ children }: { children: ReactNode }) {
    const [items, setItems] = useState<AppNotification[]>([]);

    const add = useCallback((notification: AppNotification) => setItems(prev => [notification, ...prev]), []);
    const markAllRead = useCallback(() => setItems(prev => prev.map(n => ({ ...n, read: true }))), []);
    const clear = useCallback(() => setItems([]), []);

    const value = useMemo(
        () => ({ items, unreadCount: items.filter(n => !n.read).length, add, markAllRead, clear }),
        [items, add, markAllRead, clear],
    );

    return <NotificationsContext value={value}>{children}</NotificationsContext>;
}

export function useNotifications() {
    const ctx = use(NotificationsContext);

    if (!ctx) {
throw new Error('useNotifications() must be used inside <NotificationsProvider>');
}

    return ctx;
}
