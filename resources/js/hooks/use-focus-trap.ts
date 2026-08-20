import { useEffect, useRef  } from 'react';
import type {RefObject} from 'react';

/**
 * Trap keyboard focus inside `container` while `active` is true, and restore it
 * to the previously-focused element on close. Radix handles this for its own
 * dialogs/sheets; use this for bespoke overlays (e.g. the mobile sidebar drawer).
 *
 * Pair it with `inert` on the background content so assistive tech also skips it.
 */
export const useFocusTrap = (container: RefObject<HTMLElement | null>, active: boolean, onEscape?: () => void) => {
    const escapeRef = useRef(onEscape);

    // Kept in a ref so a caller passing a fresh closure each render does not
    // tear down and rebuild the trap. Assigned in an effect rather than during
    // render, which React forbids.
    useEffect(() => {
        escapeRef.current = onEscape;
    }, [onEscape]);

    useEffect(() => {
        if (!active) {
return;
}

        const previouslyFocused = document.activeElement as HTMLElement | null;

        const focusable = () => {
            const el = container.current;

            if (!el) {
return [] as HTMLElement[];
}

            const selector =
                'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

            return Array.from(el.querySelectorAll<HTMLElement>(selector)).filter(n => n.offsetParent !== null);
        };

        const onKeydown = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                escapeRef.current?.();

                return;
            }

            if (e.key !== 'Tab') {
return;
}

            const items = focusable();

            if (!items.length) {
return;
}

            const first = items[0];
            const last = items[items.length - 1];
            const current = document.activeElement as HTMLElement | null;
            const inside = !!container.current?.contains(current);

            if (e.shiftKey && (current === first || !inside)) {
                e.preventDefault();
                last?.focus();
            } else if (!e.shiftKey && current === last) {
                e.preventDefault();
                first?.focus();
            }
        };

        document.addEventListener('keydown', onKeydown, true);
        const raf = requestAnimationFrame(() => focusable()[0]?.focus());

        return () => {
            cancelAnimationFrame(raf);
            document.removeEventListener('keydown', onKeydown, true);
            previouslyFocused?.focus?.();
        };
    }, [active, container]);
};
