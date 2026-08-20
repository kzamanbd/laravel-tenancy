import type { ApexOptions } from 'apexcharts';
import { lazy, Suspense, useMemo, useSyncExternalStore } from 'react';
import { useTheme } from '@/context/theme-context';

// Lazy: keep apexcharts (~290KB gz) out of the eager bundle. The chart lib is
// fetched only when a chart mounts, and the skeleton fallback shows until then.
const ReactApexChart = lazy(() => import('react-apexcharts'));

export type ApexChartProps = {
    // Loosely typed on purpose: the chart configs in the pages' `data.ts` files are
    // plain object literals, so `chart.type` widens to `string`. Narrowed internally.
    options?: ApexOptions | Record<string, unknown>;
    series?: unknown[];
    type?: string;
    hideLoader?: boolean;
    height?: string | number;
    width?: string | number;
    className?: string;
};

export default function ApexChart({
    options = {},
    series = [],
    type,
    hideLoader = false,
    height = 'auto',
    width,
    className,
}: ApexChartProps) {
    // Direction-aware: ApexCharts needs chart.rtl=true to mirror under dir="rtl".
    // Source the direction from the theme cookie so charts re-render reactively
    // when the user toggles RTL in the customizer.
    const { settings } = useTheme();
    const isRtl = settings.rtlClass === 'rtl';

    // ApexCharts touches `window` at import time, so it cannot run during
    // Inertia's server render. Subscribing to a store that never changes gives
    // false on the server and true on the client without a state update in an
    // effect, so the skeleton is what gets server-rendered and hydration
    // matches.
    const isClient = useSyncExternalStore(
        () => () => {},
        () => true,
        () => false,
    );

    // Keep reactive: parents may mutate options/series after mount (e.g. theme colors).
    const chartOptions = useMemo<ApexOptions>(() => {
        const base = options as ApexOptions;

        return { ...base, chart: { ...base.chart, rtl: isRtl } };
    }, [options, isRtl]);

    const chartType = (chartOptions.chart?.type || type || 'bar') as string;
    const calculatedHeight =
        height === 'auto' || height == null ? '240px' : typeof height === 'number' ? `${height}px` : height;

    // Skeleton adapts to the real chart: number of bars/points follows the data.
    const pointCount = useMemo(() => {
        const cats = (chartOptions as { xaxis?: { categories?: unknown[] } })?.xaxis?.categories?.length;
        const first = Array.isArray(series) ? (series[0] as { data?: unknown[] })?.data?.length : 0;

        return Math.min(Math.max(cats || first || 9, 5), 16);
    }, [chartOptions, series]);

    // Deterministic, varied bar heights (no Math.random → stable across renders).
    const barHeights = useMemo(
        () => Array.from({ length: pointCount }, (_, i) => 28 + ((i * 37 + 13) % 62)),
        [pointCount],
    );
    const axisTicks = Math.min(pointCount, 6);

    const bars = (
        <div className='flex flex-1 items-end gap-2'>
            {barHeights.map((h, n) => (
                <div key={n} className='sk flex-1 rounded-t-md' style={{ height: `${h}%` }}></div>
            ))}
        </div>
    );
    const ticks = (
        <div className='flex justify-between gap-2'>
            {Array.from({ length: axisTicks }, (_, n) => (
                <div key={n} className='sk h-2.5 w-8 rounded'></div>
            ))}
        </div>
    );

    const fallback = hideLoader ? null : (
        <div
            style={{ height: calculatedHeight }}
            className='mx-2 my-2 w-[calc(100%-1rem)] rounded-xl border border-border bg-card p-4 select-none dark:border-white/10'>
            {['bar', 'candlestick', 'boxPlot'].includes(chartType) ? (
                /* Bar / column */
                <div className='flex h-full flex-col gap-3'>
                    {bars}
                    {ticks}
                </div>
            ) : ['line', 'area'].includes(chartType) ? (
                /* Line / area */
                <div className='flex h-full flex-col gap-3'>
                    <div className='relative flex-1 overflow-hidden'>
                        <svg
                            className='sk-line h-full w-full'
                            viewBox='0 0 100 40'
                            preserveAspectRatio='none'
                            fill='none'>
                            <defs>
                                <linearGradient id='sk-fill' x1='0' y1='0' x2='0' y2='1'>
                                    <stop offset='0%' stopColor='currentColor' stopOpacity='0.25' />
                                    <stop offset='100%' stopColor='currentColor' stopOpacity='0' />
                                </linearGradient>
                            </defs>
                            <path
                                d='M0 30 C 12 26, 18 12, 28 16 C 38 20, 44 32, 54 27 C 64 22, 70 8, 80 13 C 90 18, 95 28, 100 22 V 40 H 0 Z'
                                fill='url(#sk-fill)'
                            />
                            <path
                                d='M0 30 C 12 26, 18 12, 28 16 C 38 20, 44 32, 54 27 C 64 22, 70 8, 80 13 C 90 18, 95 28, 100 22'
                                stroke='currentColor'
                                strokeWidth='1.5'
                                strokeOpacity='0.35'
                                vectorEffect='non-scaling-stroke'
                            />
                        </svg>
                    </div>
                    {ticks}
                </div>
            ) : ['pie', 'donut', 'radialBar'].includes(chartType) ? (
                /* Pie / donut / radialBar */
                <div className='flex h-full items-center justify-center gap-8'>
                    <div className='sk relative aspect-square h-3/4 max-h-40 rounded-full'>
                        {['donut', 'radialBar'].includes(chartType) && (
                            <div className='absolute inset-[22%] rounded-full bg-card'></div>
                        )}
                    </div>
                    <div className='flex flex-col gap-3'>
                        {[1, 2, 3].map(n => (
                            <div key={n} className='flex items-center gap-2'>
                                <div className='sk size-3 rounded-full'></div>
                                <div className='sk h-2.5 rounded' style={{ width: `${80 - n * 14}px` }}></div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : (
                /* Fallback */
                <div className='flex h-full flex-col gap-3'>
                    {bars}
                    <div className='sk h-2.5 w-3/4 rounded'></div>
                </div>
            )}
        </div>
    );

    if (!isClient) {
        return fallback;
    }

    return (
        <Suspense fallback={fallback}>
            {/* key on direction: ApexCharts only reads chart.rtl at init; updating it via
                updateOptions() mis-measures width and collapses the chart to a sliver.
                Remount on toggle so it re-inits cleanly. */}
            <ReactApexChart
                key={isRtl ? 'rtl' : 'ltr'}
                options={chartOptions}
                series={series as never}
                type={chartType as never}
                height={height}
                width={width}
                className={className}
            />
        </Suspense>
    );
}
