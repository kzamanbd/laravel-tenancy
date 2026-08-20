import { createContext, use, useCallback, useMemo, useState  } from 'react';
import type {ReactNode} from 'react';

// Transient, app-wide UI state (not persisted). Theme *configuration* lives in
// the `useTheme()` cookie; this context holds ephemeral chrome state that several
// unrelated components need to share — e.g. whether the mobile sidebar is open.
interface UiContextValue {
    isMobileMenuOpen: boolean;
    setMobileMenuOpen: (open: boolean) => void;
    toggleMobileMenu: () => void;
    closeMobileMenu: () => void;
    // Open-state for the app-wide overlays that are triggered from a different
    // component than the one defining them (navbar buttons open the search,
    // activity drawer and theme customizer). Radix dialogs are component-scoped,
    // so the shared open-state lives here instead of relying on global element ids.
    searchOpen: boolean;
    setSearchOpen: (open: boolean) => void;
    activityOpen: boolean;
    setActivityOpen: (open: boolean) => void;
    customizerOpen: boolean;
    setCustomizerOpen: (open: boolean) => void;
}

const UiContext = createContext<UiContextValue | null>(null);

export function UiProvider({ children }: { children: ReactNode }) {
    const [isMobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [searchOpen, setSearchOpen] = useState(false);
    const [activityOpen, setActivityOpen] = useState(false);
    const [customizerOpen, setCustomizerOpen] = useState(false);

    const toggleMobileMenu = useCallback(() => setMobileMenuOpen(open => !open), []);
    const closeMobileMenu = useCallback(() => setMobileMenuOpen(false), []);

    const value = useMemo(
        () => ({
            isMobileMenuOpen,
            setMobileMenuOpen,
            toggleMobileMenu,
            closeMobileMenu,
            searchOpen,
            setSearchOpen,
            activityOpen,
            setActivityOpen,
            customizerOpen,
            setCustomizerOpen,
        }),
        [isMobileMenuOpen, toggleMobileMenu, closeMobileMenu, searchOpen, activityOpen, customizerOpen],
    );

    return <UiContext value={value}>{children}</UiContext>;
}

export function useUi() {
    const ctx = use(UiContext);

    if (!ctx) {
throw new Error('useUi() must be used inside <UiProvider>');
}

    return ctx;
}
