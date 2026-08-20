import { createContext, use, useCallback, useEffect, useMemo, useRef  } from 'react';
import type {ReactNode} from 'react';
import { useUi } from '@/context/ui-context';
import { useCookieState } from '@/hooks/use-cookie-state';

export interface Settings {
    theme: 'light' | 'dark' | 'system';
    themeVariant: string;
    rtlClass: 'ltr' | 'rtl';
    menu: 'vertical' | 'collapsible' | 'horizontal';
    animation: string;
    navbar: 'navbar-fixed' | 'navbar-static' | 'navbar-hidden';
    footer: 'footer-fixed' | 'footer-static' | 'footer-hidden';
    semiDark: boolean;
    collapsable: boolean;
}

export const defaultSettings = (): Settings => {
    return {
        theme: 'system',
        themeVariant: 'default',
        rtlClass: 'ltr',
        menu: 'vertical',
        animation: 'animate__fadeIn',
        navbar: 'navbar-fixed',
        footer: 'footer-fixed',
        semiDark: false,
        collapsable: false,
    };
};

interface ThemeContextValue {
    settings: Settings;
    update: (patch: Partial<Settings>) => void;
    resetTheme: () => void;
    wrapperClass: string;
    navbarClass: string;
    footerClass: string;
    verticalMenuClass: string;
    toggleSidebar: () => void;
    toggleCollapsible: () => void;
    setMenuLayout: (val: Settings['menu']) => void;
    isMobileMenuOpen: boolean;
    toggleMobileMenu: () => void;
}

const ThemeContext = createContext<ThemeContextValue | null>(null);

// Changing the theme recolors most of the UI at once; elements with
// `transition-colors`/`transition-all` then animate together, which reads as lag.
// Disable transitions for a single frame while the new theme is applied.
const suppressThemeTransition = () => {
    const el = document.documentElement;
    el.classList.add('theme-no-transition');
    requestAnimationFrame(() => {
        requestAnimationFrame(() => el.classList.remove('theme-no-transition'));
    });
};

export function ThemeProvider({ children }: { children: ReactNode }) {
    // Mobile sidebar open-state lives in the UI context so the navbar toggle and
    // the sidebar — separate components, separate useTheme() calls — share one
    // source of truth. Theme *config* stays in the cookie below.
    const { isMobileMenuOpen, setMobileMenuOpen, toggleMobileMenu } = useUi();
    const [settings, setSettings] = useCookieState<Settings>('themeConfig', defaultSettings);

    const update = useCallback(
        (patch: Partial<Settings>) => {
            setSettings(prev => {
                const next = { ...prev, ...patch };

                // Switching the base theme clears semi-dark; enabling semi-dark
                // forces the base theme back to `system` (mirrors the Nuxt watchers).
                if (patch.theme !== undefined && patch.theme !== prev.theme) {
next.semiDark = false;
}

                if (patch.semiDark === true && prev.semiDark !== true) {
next.theme = 'system';
}

                return next;
            });
        },
        [setSettings],
    );

    const resetTheme = useCallback(() => setSettings(defaultSettings()), [setSettings]);

    // ---- <html> attribute sync -------------------------------------------------
    const { theme, themeVariant, rtlClass, semiDark, animation, menu, navbar, footer, collapsable } = settings;

    // Only fire the transition-suppression when a colour-affecting setting changes;
    // any *other* cookie write (e.g. collapsing the sidebar) must not suppress the
    // sidebar's own width/margin transition.
    const colorKey = `${theme}|${themeVariant}|${semiDark}|${rtlClass}`;
    const previousColorKey = useRef(colorKey);

    useEffect(() => {
        const el = document.documentElement;

        if (previousColorKey.current !== colorKey) {
            previousColorKey.current = colorKey;
            suppressThemeTransition();
        }

        const applyTheme = () => {
            const isDark =
                theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            el.classList.toggle('dark', isDark);
            el.classList.toggle('light', !isDark);
        };

        applyTheme();

        // Drop any previously applied variant class before adding the current one.
        el.classList.forEach(cls => {
            if (cls.startsWith('theme-') && cls !== 'theme-no-transition') {
el.classList.remove(cls);
}
        });
        el.classList.add(`theme-${themeVariant}`);
        el.dir = rtlClass;

        if (theme !== 'system') {
return;
}

        const media = window.matchMedia('(prefers-color-scheme: dark)');
        media.addEventListener('change', applyTheme);

        return () => media.removeEventListener('change', applyTheme);
    }, [theme, themeVariant, rtlClass, colorKey]);

    // ---- window listeners ------------------------------------------------------
    useEffect(() => {
        const handleScroll = () => {
            const el = document.querySelector('.navbar-nav');

            if (!el) {
return;
}

            el.classList.toggle('scrollable', window.scrollY > 0);
        };

        const handleResize = () => {
            if (window.innerWidth < 1024) {
                setSettings(prev => (prev.collapsable ? { ...prev, collapsable: false } : prev));
            } else {
                setMobileMenuOpen(false);
            }
        };

        // Enforce the mobile invariant on load too: a `collapsable=true` persisted from a
        // desktop session must not carry into a fresh narrow-viewport load (resize alone
        // wouldn't fire). Runs before listeners so the first paint is already correct.
        handleResize();
        handleScroll();

        window.addEventListener('scroll', handleScroll);
        window.addEventListener('resize', handleResize);

        return () => {
            window.removeEventListener('scroll', handleScroll);
            window.removeEventListener('resize', handleResize);
        };
    }, [setSettings, setMobileMenuOpen]);

    // ---- actions ---------------------------------------------------------------
    // The single sidebar toggle (navbar button). On desktop it collapses the
    // sidebar to the mini icon-rail and back (Gemini/ChatGPT style); on mobile it
    // opens/closes the slide-in drawer.
    const toggleSidebar = useCallback(() => {
        if (window.innerWidth < 1024) {
            toggleMobileMenu();
        } else {
            setSettings(prev => ({ ...prev, collapsable: !prev.collapsable }));
        }
    }, [toggleMobileMenu, setSettings]);

    const toggleCollapsible = useCallback(() => {
        setSettings(prev => ({ ...prev, collapsable: !prev.collapsable }));
    }, [setSettings]);

    const setMenuLayout = useCallback(
        (val: Settings['menu']) => {
            setSettings(prev => ({ ...prev, menu: val, collapsable: val === 'collapsible' }));
        },
        [setSettings],
    );

    // ---- computed classes ------------------------------------------------------
    const wrapperClass = [
        'tw--wrapper',
        collapsable && 'collapsed-menu',
        menu === 'horizontal' && 'horizontal',
        (menu === 'vertical' || menu === 'collapsible') && 'vertical',
        animation,
    ]
        .filter(Boolean)
        .join(' ');

    const value = useMemo<ThemeContextValue>(
        () => ({
            settings,
            update,
            resetTheme,
            wrapperClass,
            navbarClass: `navbar-nav ${navbar}`,
            footerClass: `footer ${footer}`,
            verticalMenuClass: `vertical-menu ${semiDark ? 'semi-dark' : ''} ${isMobileMenuOpen ? 'expanded' : ''}`,
            toggleSidebar,
            toggleCollapsible,
            setMenuLayout,
            isMobileMenuOpen,
            toggleMobileMenu,
        }),
        [
            settings,
            update,
            resetTheme,
            wrapperClass,
            navbar,
            footer,
            semiDark,
            isMobileMenuOpen,
            toggleSidebar,
            toggleCollapsible,
            setMenuLayout,
            toggleMobileMenu,
        ],
    );

    return <ThemeContext value={value}>{children}</ThemeContext>;
}

export function useTheme() {
    const ctx = use(ThemeContext);

    if (!ctx) {
throw new Error('useTheme() must be used inside <ThemeProvider>');
}

    return ctx;
}
