import { useId } from 'react';

export default function AppLogo({ className }: { className?: string }) {
    // Unique gradient ids per instance. Multiple AppLogo instances coexist in the
    // DOM (sidebar + navbar); with shared ids, `url(#id)` resolves to the first
    // match in document order — and when that first instance sits inside a
    // `display:none` SVG (e.g. the hidden vertical-menu in horizontal layout),
    // WebKit drops its paint server, blanking the visible logo. useId() avoids it.
    const id = useId();
    const gradRing = `${id}-ring`;
    const gradDot = `${id}-dot`;

    // Caller-provided class replaces the default size (avoids tw-logo-icon size clashes).
    return (
        <svg
            className={className || 'tw-logo-icon'}
            xmlns='http://www.w3.org/2000/svg'
            viewBox='0 0 100 100'
            width='512'
            height='512'>
            <defs>
                <linearGradient id={gradRing} x1='0.2' y1='0' x2='0.8' y2='1'>
                    <stop offset='0' stopColor='#6366F1'></stop>
                    <stop offset='0.55' stopColor='#4338CA'></stop>
                    <stop offset='1' stopColor='#1E1B4B'></stop>
                </linearGradient>
                <linearGradient id={gradDot} x1='0' y1='0' x2='1' y2='1'>
                    <stop offset='0' stopColor='#A5B4FC'></stop>
                    <stop offset='1' stopColor='#6366F1'></stop>
                </linearGradient>
            </defs>
            {/* rounded-square frame with a diamond riding its top-right corner */}
            <rect
                x='20'
                y='20'
                width='60'
                height='60'
                rx='20'
                fill='none'
                stroke={`url(#${gradRing})`}
                strokeWidth='14'></rect>
            <rect
                x='70'
                y='10'
                width='20'
                height='20'
                rx='5'
                transform='rotate(45 80 20)'
                fill={`url(#${gradDot})`}></rect>
        </svg>
    );
}
