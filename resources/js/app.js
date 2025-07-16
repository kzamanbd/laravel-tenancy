/**
 * Metricon
 * @version 1.0.0
 * @description A simple UI framework for building beautiful responsive websites and web apps.
 * @license MIT
 * @see https://draftscripts.com/
 */

/**
 * Core Dependencies
 */
import "preline";
import "simplebar";

// You will need a ResizeObserver polyfill for browsers that don't support it! (iOS Safari, Edge, ...)
import ResizeObserver from "resize-observer-polyfill";
window.ResizeObserver = ResizeObserver;

console.log(
    "%cMetricon is a simple UI framework for building beautiful responsive websites and web apps.\n%cVisit https://draftscripts.com for more information.\n\n%c🚀 Metricon %c1.0.0 %cby %cDraftscripts",

    // Description
    "color: #000; font-weight: bold; font-size: 16px;",
    "color: #000; font-size: 14px;",

    // Badges (in new order)
    "color: #fff; background: #000; padding: 2px 4px; border-radius: 2px;",
    "color: #fff; background: #007bff; padding: 2px 4px; border-radius: 2px;",
    "color: #fff; background: #28a745; padding: 2px 4px; border-radius: 2px;",
    "color: #fff; background: #dc3545; padding: 2px 4px; border-radius: 2px;"
);

const twSelector = (el, multiple = false) => {
    if (multiple) {
        if (typeof el === "string") {
            return document.querySelectorAll(el);
        }
        return el;
    }
    return document.querySelector(el);
};

(function () {
    const root = document.documentElement;
    const twWrapper = twSelector(".tw--wrapper");
    const twContent = twSelector(".tw--content");
    const verticalMenu = twSelector(".vertical-menu");
    const navbar = twSelector(".navbar-nav");
    const footer = twSelector("footer.footer");
    const SETTINGS_KEY = "themeConfig";

    const initialize = {
        theme: "system",
        themeVariant: "default",
        rtlClass: "ltr",
        menu: "vertical",
        layout: "full-layout",
        animation: "animate__fadeIn",
        navbar: "navbar-fixed",
        footer: "footer-fixed",
        semiDark: false,
        hiddenSidebar: false,
        collapsable: false,
    };

    const saveSettings = (key, value) => {
        const settings = {
            ...initialize,
            ...(JSON.parse(localStorage.getItem(SETTINGS_KEY)) || {}),
        };
        if (key) settings[key] = value;
        localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));
    };

    const getSettings = (key = null) => {
        const settings =
            JSON.parse(localStorage.getItem(SETTINGS_KEY)) || initialize;
        if (key)
            return settings[key] !== undefined
                ? settings[key]
                : initialize[key];
        if (settings.theme === "system") {
            const isDarkMode =
                window.matchMedia &&
                window.matchMedia("(prefers-color-scheme: dark)").matches;
            return {
                ...settings,
                theme: isDarkMode ? "dark" : "light",
            };
        }
        return settings;
    };

    const loadSettings = () => {
        const settings = getSettings();
        if (!settings) return;

        root.classList.add(settings.theme);
        root.setAttribute("dir", settings.rtlClass);
        root.classList.add(`theme-${settings.themeVariant}`);

        if (!twWrapper) return;
        twWrapper.classList.add(settings.layout);
        navbar.classList.add(settings.navbar);
        footer.classList.add(settings.footer);
        if (settings.semiDark) verticalMenu.classList.add("semi-dark");
        if (settings.collapsable) twWrapper.classList.add("collapsed-menu");
        if (settings.hiddenSidebar) twWrapper.classList.add("hidden-menu");

        // active state
        const semiDarkSidebar = twSelector(".sidebar-semi-dark");
        if (semiDarkSidebar) semiDarkSidebar.checked = settings.semiDark;

        const themeSwitcher = twSelector(
            `.theme-switcher[value="${settings.theme}"]`
        );
        if (themeSwitcher) themeSwitcher.checked = true;
        const themeToggle = twSelector(`.theme-switcher[value="toggle"]`);
        if (settings.theme === "dark" && themeToggle)
            themeToggle.checked = true;

        const colorSchema = twSelector(
            `.color-schema[value="${settings.themeVariant}"]`
        );
        if (colorSchema) colorSchema.checked = true;

        const navPosition = twSelector(
            `.navigation-position[value="${settings.menu}"]`
        );
        if (navPosition) navPosition.checked = true;

        const layoutStyle = twSelector(
            `.layout-style[value="${settings.layout}"]`
        );
        if (layoutStyle) layoutStyle.checked = true;

        const layoutDirection = twSelector(
            `.layout-direction[value="${settings.rtlClass}"]`
        );
        if (layoutDirection) layoutDirection.checked = true;

        const navbarType = twSelector(
            `[name="navbarType"][value="${settings.navbar}"]`
        );
        if (navbarType) navbarType.checked = true;

        const footerType = twSelector(
            `[name="footerType"][value="${settings.footer}"]`
        );
        if (footerType) footerType.checked = true;
        const sidebar = twSelector(".hidden-sidebar");
        if (sidebar.type == "checkbox")
            sidebar.checked = settings.hiddenSidebar;

        // Set animation class
        twContent.classList.add("animate__animated", settings.animation);
        twContent.classList.add(settings.animation);
        const animationSwitcher = twSelector("#animation-switcher");
        if (animationSwitcher) {
            animationSwitcher.value = settings.animation;
        }

        // origin is horizontal
        if (window.location.pathname.includes("horizontal")) {
            twWrapper.classList.add("horizontal");
            twWrapper.classList.remove("vertical", "collapsible");
            twWrapper.classList.remove("collapsed-menu", "expanded");
        }
    };

    // Load settings on page load
    loadSettings();

    // check if twWrapper exists before proceeding
    if (!twWrapper) {
        return;
    }

    // Set footer year
    const footerText = twSelector("#footer-year");
    footerText.innerHTML = `© 2024 - ${new Date().getFullYear()}`;

    // Remove screen loader
    twSelector(".loading")?.remove();

    // Active menu logic
    const currentPath = window.location.href;

    const menuItem = twSelector(`.tw-nav-menu a[href="${currentPath}"]`);
    if (menuItem) {
        menuItem.classList.add("active");
        const target = menuItem
            .closest(".twd--menu")
            ?.closest(".tw-menu-item")
            ?.querySelector(".tw-menu-link");
        if (target) {
            target.parentElement.classList.add("active");
            target.nextElementSibling.style.display = "block";
        }
        let parentMenu = menuItem.closest(".twd--menu-item.hs-accordion");
        while (parentMenu) {
            parentMenu.classList.add("active");
            parentMenu
                .querySelector(".hs-accordion-toggle")
                ?.classList.add("active");
            parentMenu
                .querySelector(".hs-accordion-content")
                ?.style.setProperty("display", "block");
            parentMenu = parentMenu
                .closest(".twd--menu-item.hs-accordion")
                ?.parentElement.closest(".hs-accordion");
        }
    }

    // Scroll sidebar menu into view
    const viewPort =
        twSelector(".tw-nav-menu .twd--link.active") ||
        twSelector(".tw-nav-menu .tw-menu-link.active");
    viewPort?.scrollIntoView({ block: "center", behavior: "smooth" });

    // Sidebar toggle on resize
    window.addEventListener("resize", () => {
        if (!verticalMenu) return;
        window.innerWidth < 1024
            ? twWrapper.classList.remove("collapsed-menu")
            : verticalMenu.classList.remove("expanded");
    });

    // Scroll header effect
    window.addEventListener("scroll", () => {
        if (!navbar) return;
        navbar.classList.toggle("scrollable", root.scrollTop > 0);
    });

    // Sidebar toggle buttons
    const toggleItems = [
        ...twSelector(".hidden-sidebar", true),
        ...twSelector(".toggle-sidebar", true),
        twSelector(".menu-shadow"),
    ].filter(Boolean);

    toggleItems.forEach((item) => {
        item.addEventListener("click", () => {
            if (item.classList.contains("hidden-sidebar")) {
                const status = !getSettings("hiddenSidebar");
                saveSettings("hiddenSidebar", status);
                twWrapper.classList.toggle("hidden-menu", status);
            } else {
                if (window.innerWidth < 1024) {
                    twSelector(".menu-shadow")?.classList.toggle("hidden");
                    verticalMenu.classList.toggle("expanded");
                } else {
                    twWrapper.classList.toggle("collapsed-menu");
                }
                saveSettings(
                    "collapsable",
                    twWrapper.classList.contains("collapsed-menu")
                );
            }
        });
    });

    // Theme switcher
    twSelector(".theme-switcher", true).forEach((item) => {
        item.addEventListener("change", () => {
            const isToggle = item.value === "toggle";
            const newTheme = isToggle
                ? item.checked
                    ? "dark"
                    : "system"
                : item.value;
            root.classList.remove("light", "dark", "system");
            root.classList.add(newTheme);
            saveSettings("theme", newTheme);
        });
    });

    // Color schema switcher
    twSelector(".color-schema", true).forEach((item) => {
        item.addEventListener("click", () => {
            const variant = item.value;
            root.classList.remove(
                "theme-default",
                "theme-amber",
                "theme-rose",
                "theme-purple",
                "theme-sky",
                "theme-teal"
            );
            root.classList.add(`theme-${variant}`);
            saveSettings("themeVariant", variant);
        });
    });

    // Semi-dark sidebar toggle
    twSelector(".sidebar-semi-dark").addEventListener("change", () => {
        verticalMenu.classList.toggle("semi-dark");
        saveSettings("semiDark", verticalMenu.classList.contains("semi-dark"));
    });

    // Navbar type toggle
    twSelector('[name="navbarType"]', true).forEach((item) => {
        item.addEventListener("change", () => {
            navbar.className = `navbar-nav ${item.value}`;
            saveSettings("navbar", item.value);
        });
    });

    // Footer type toggle
    twSelector('[name="footerType"]', true).forEach((item) => {
        item.addEventListener("change", () => {
            footer.className = `footer ${item.value}`;
            saveSettings("footer", item.value);
        });
    });

    // Layout type toggle
    twSelector(".layout-style", true).forEach((item) => {
        item.addEventListener("change", () => {
            twWrapper.classList.remove("boxed-layout", "full");
            twWrapper.classList.add(item.value);
            saveSettings("layout", item.value);
        });
    });

    // Layout direction toggle
    twSelector(".layout-direction", true).forEach((item) => {
        item.addEventListener("change", () => {
            root.setAttribute("dir", item.value);
            saveSettings("rtlClass", item.value);
        });
    });

    // Navigation position toggle
    twSelector(".navigation-position", true).forEach((item) => {
        item.addEventListener("change", () => {
            const position = item.value;
            saveSettings("menu", position);
            if (position === "collapsible") {
                twWrapper.classList.toggle("collapsed-menu");
                saveSettings(
                    "collapsable",
                    twWrapper.classList.contains("collapsed-menu")
                );
            } else if (
                position === "horizontal" &&
                !window.location.pathname.includes("horizontal")
            ) {
                window.location.href = "/pages/layouts/horizontal.html";
            } else {
                twWrapper.classList.remove(
                    "horizontal",
                    "vertical",
                    "collapsed-menu"
                );
                twWrapper.classList.add(position);
            }
        });
    });

    // animation settings
    twSelector("#animation-switcher").addEventListener("change", (event) => {
        const animation = event.target.value;
        twContent.classList.remove(
            "animate__fadeIn",
            "animate__fadeInDown",
            "animate__fadeInUp",
            "animate__fadeInLeft",
            "animate__fadeInRight",
            "animate__slideInDown",
            "animate__slideInLeft",
            "animate__slideInRight",
            "animate__zoomIn"
        );
        twContent.classList.add("animate__animated", animation);
        saveSettings("animation", animation);
    });
})();
