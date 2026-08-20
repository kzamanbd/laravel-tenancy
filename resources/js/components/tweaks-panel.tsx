import AppLogo from '@/components/app-logo';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { useTheme  } from '@/context/theme-context';
import type {Settings} from '@/context/theme-context';
import { useUi } from '@/context/ui-context';
import { cn } from '@/lib/utils';

const colors = [
    { value: 'default', class: 'bg-indigo-500', label: 'Indigo' },
    { value: 'amber', class: 'bg-amber-500', label: 'Amber' },
    { value: 'rose', class: 'bg-rose-500', label: 'Rose' },
    { value: 'purple', class: 'bg-purple-500', label: 'Purple' },
    { value: 'sky', class: 'bg-sky-500', label: 'Sky' },
    { value: 'teal', class: 'bg-teal-500', label: 'Teal' },
];

const themeModes = [
    { value: 'system', label: 'Auto' },
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
] as const;

const menuOptions = [
    { value: 'vertical', label: 'Vertical' },
    { value: 'collapsible', label: 'Collapsed' },
    { value: 'horizontal', label: 'Horizontal' },
] as const;

const navbarOptions = [
    { value: 'navbar-static', label: 'Static' },
    { value: 'navbar-fixed', label: 'Fixed' },
    { value: 'navbar-hidden', label: 'Hidden' },
] as const;

const footerOptions = [
    { value: 'footer-static', label: 'Static' },
    { value: 'footer-fixed', label: 'Fixed' },
    { value: 'footer-hidden', label: 'Hidden' },
] as const;

// Check mark shown inside the selected radio/checkbox proxy.
const CheckDot = () => {
    return (
        <span className='flex size-4 items-center justify-center rounded-full border border-border transition-colors group-has-checked:border-primary group-has-checked:bg-primary'>
            <span className='icon-[mdi--check] size-3 text-white opacity-0 group-has-checked:opacity-100'></span>
        </span>
    );
};

// Segmented pill used by the direction / navbar / footer pickers.
const SegmentedOption = ({
    name,
    value,
    checked,
    label,
    onChange,
    uppercase = false,
}: {
    name: string;
    value: string;
    checked: boolean;
    label: string;
    onChange: () => void;
    uppercase?: boolean;
}) => {
    return (
        <label className='group cursor-pointer'>
            <input type='radio' name={name} value={value} checked={checked} onChange={onChange} className='sr-only' />
            <span
                className={cn(
                    'block rounded-md px-2 py-1.5 text-center text-sm font-medium text-muted-foreground transition-all group-has-checked:bg-card group-has-checked:text-foreground group-has-checked:shadow-sm dark:group-has-checked:bg-background',
                    uppercase && 'px-3 uppercase',
                )}>
                {label}
            </span>
        </label>
    );
};

export default function TweaksPanel() {
    const { customizerOpen, setCustomizerOpen } = useUi();
    const { settings, update, setMenuLayout, resetTheme } = useTheme();

    const activeColorLabel = colors.find(c => c.value === settings.themeVariant)?.label ?? 'Indigo';

    return (
        <div id='tweaks-panel'>
            <button
                type='button'
                className='customizer-icon'
                aria-label='Open theme customizer'
                onClick={() => setCustomizerOpen(true)}>
                <svg
                    xmlns='http://www.w3.org/2000/svg'
                    width='24'
                    height='24'
                    viewBox='0 0 24 24'
                    fill='none'
                    stroke='currentColor'
                    strokeWidth='2'
                    strokeLinecap='round'
                    strokeLinejoin='round'
                    className='lucide lucide-palette size-4'
                    aria-hidden='true'>
                    <circle cx='13.5' cy='6.5' r='.5' fill='currentColor'></circle>
                    <circle cx='17.5' cy='10.5' r='.5' fill='currentColor'></circle>
                    <circle cx='8.5' cy='7.5' r='.5' fill='currentColor'></circle>
                    <circle cx='6.5' cy='12.5' r='.5' fill='currentColor'></circle>
                    <path d='M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z'></path>
                </svg>
            </button>

            <Sheet open={customizerOpen} onOpenChange={setCustomizerOpen}>
                <SheetContent
                    side='right'
                    className='w-full gap-0 p-0 sm:max-w-sm'
                    aria-label='Theme customizer'
                    aria-describedby={undefined}>
                    {/* Header */}
                    <div className='flex items-center justify-between border-b border-border bg-linear-to-br from-primary-50 to-purple-50 px-5 py-4 dark:border-card dark:from-card dark:to-background'>
                        <div className='flex items-center gap-3'>
                            <AppLogo className='size-8' />
                            <div>
                                <SheetTitle asChild>
                                    <h5 className='text-base font-bold text-foreground'>Theme Customizer</h5>
                                </SheetTitle>
                                <p className='text-xs text-muted-foreground'>Live preview · saved automatically</p>
                            </div>
                        </div>
                    </div>

                    {/* Body */}
                    <div data-simplebar className='min-h-0 flex-1 overflow-y-auto'>
                        {/* Color Scheme */}
                        <section className='px-5 py-4'>
                            <div className='mb-1 flex items-center gap-1.5'>
                                <span className='icon-[mdi--creation] size-4 text-primary'></span>
                                <h6 className='text-sm font-bold text-foreground'>Color Scheme</h6>
                            </div>
                            <p className='mb-3 text-xs text-muted-foreground'>
                                Light, dark, or follow your system preference.
                            </p>
                            <div className='grid grid-cols-3 gap-2'>
                                {themeModes.map(opt => (
                                    <label
                                        key={opt.value}
                                        className='group cursor-pointer rounded-xl border border-border p-1.5 transition-all has-checked:border-primary has-checked:ring-1 has-checked:ring-primary dark:border-card'>
                                        <input
                                            type='radio'
                                            name='theme-mode'
                                            value={opt.value}
                                            checked={settings.theme === opt.value}
                                            onChange={() => update({ theme: opt.value })}
                                            className='sr-only'
                                        />
                                        {/* Mock window */}
                                        <div
                                            className={`flex h-14 gap-1 overflow-hidden rounded-lg border p-1 ${opt.value === 'dark' ? 'border-border bg-gray-900' : 'border-border bg-card'}`}>
                                            <div className='flex flex-col gap-0.5 pt-0.5'>
                                                <span
                                                    className={`size-1 rounded-full ${opt.value === 'dark' ? 'bg-muted-foreground' : 'bg-muted'}`}></span>
                                                <span
                                                    className={`size-1 rounded-full ${opt.value === 'dark' ? 'bg-muted-foreground' : 'bg-muted'}`}></span>
                                            </div>
                                            <div className='flex-1 space-y-1 pt-0.5'>
                                                <span
                                                    className={`block h-1 w-3/4 rounded ${opt.value === 'dark' ? 'bg-gray-700' : 'bg-muted'}`}></span>
                                                <span className='block h-2.5 w-9 rounded bg-primary'></span>
                                                <span
                                                    className={`block h-1 w-1/2 rounded ${opt.value === 'dark' ? 'bg-gray-700' : 'bg-muted'}`}></span>
                                            </div>
                                        </div>
                                        <div className='mt-1.5 flex items-center justify-between px-0.5'>
                                            <span className='text-xs font-medium text-foreground dark:text-muted-foreground'>
                                                {opt.label}
                                            </span>
                                            <CheckDot />
                                        </div>
                                    </label>
                                ))}
                            </div>

                            {/* Semi-dark sidebar */}
                            <label className='group mt-3 flex cursor-pointer items-center gap-2.5 rounded-xl border border-dashed border-border p-3 transition-colors has-checked:border-primary has-checked:bg-primary-50 dark:border-card dark:has-checked:bg-card'>
                                <input
                                    type='checkbox'
                                    checked={settings.semiDark}
                                    onChange={e => update({ semiDark: e.target.checked })}
                                    className='sr-only'
                                />
                                <span className='mt-0.5 flex size-4 shrink-0 items-center justify-center rounded border border-border transition-colors group-has-checked:border-primary group-has-checked:bg-primary'>
                                    <span className='icon-[mdi--check] size-3 text-white opacity-0 group-has-checked:opacity-100'></span>
                                </span>
                                <div>
                                    <p className='text-sm font-semibold text-foreground'>Semi-dark sidebar</p>
                                    <p className='text-xs text-muted-foreground'>Dark sidebar with a light body.</p>
                                </div>
                            </label>
                        </section>

                        {/* Primary Color */}
                        <section className='border-t border-border px-5 py-4 dark:border-card'>
                            <div className='mb-1 flex items-center justify-between'>
                                <h6 className='text-sm font-bold text-foreground'>Primary Color</h6>
                                <span className='text-xs font-medium text-muted-foreground'>{activeColorLabel}</span>
                            </div>
                            <p className='mb-3 text-xs text-muted-foreground'>Applied across portal and storefront.</p>
                            <div className='flex flex-wrap items-center gap-3'>
                                {colors.map(color => (
                                    <label key={color.value} className='group cursor-pointer'>
                                        <input
                                            type='radio'
                                            name='theme-variant'
                                            value={color.value}
                                            checked={settings.themeVariant === color.value}
                                            onChange={() => update({ themeVariant: color.value })}
                                            aria-label={color.label}
                                            className='sr-only'
                                        />
                                        <span
                                            className={`flex size-9 items-center justify-center rounded-full transition-all group-has-checked:ring-2 group-has-checked:ring-ring group-has-checked:ring-offset-2 dark:group-has-checked:ring-ring dark:group-has-checked:ring-offset-background ${color.class}`}>
                                            <span className='icon-[mdi--check] size-4 text-white opacity-0 group-has-checked:opacity-100'></span>
                                        </span>
                                    </label>
                                ))}
                            </div>
                        </section>

                        {/* Menu Layout */}
                        <section className='border-t border-border px-5 py-4 dark:border-card'>
                            <h6 className='mb-1 text-sm font-bold text-foreground'>Menu Layout</h6>
                            <p className='mb-3 text-xs text-muted-foreground'>Primary navigation paradigm.</p>
                            <div className='grid grid-cols-3 gap-2'>
                                {menuOptions.map(opt => (
                                    <label
                                        key={opt.value}
                                        className='group cursor-pointer rounded-xl border border-border p-1.5 transition-all has-checked:border-primary has-checked:ring-1 has-checked:ring-primary dark:border-card'>
                                        <input
                                            type='radio'
                                            name='menu-layout'
                                            value={opt.value}
                                            checked={settings.menu === opt.value}
                                            className='sr-only'
                                            onChange={() => setMenuLayout(opt.value as Settings['menu'])}
                                        />
                                        {/* Mock window */}
                                        <div className='flex h-14 gap-1 overflow-hidden rounded-lg border border-border bg-card p-1'>
                                            {opt.value !== 'horizontal' && (
                                                <div
                                                    className={`flex flex-col gap-0.5 rounded bg-primary/15 p-0.5 ${opt.value === 'collapsible' ? 'w-1.5' : 'w-3'}`}>
                                                    <span className='block h-1 w-full rounded-full bg-primary/40'></span>
                                                    <span className='block h-1 w-full rounded-full bg-primary/40'></span>
                                                </div>
                                            )}
                                            <div className='flex-1 space-y-1 pt-0.5'>
                                                {opt.value === 'horizontal' && (
                                                    <span className='block h-1.5 w-full rounded bg-primary/30'></span>
                                                )}
                                                <span className='block h-1 w-3/4 rounded bg-muted'></span>
                                                <span className='block h-1 w-1/2 rounded bg-muted'></span>
                                            </div>
                                        </div>
                                        <div className='mt-1.5 flex items-center justify-between px-0.5'>
                                            <span className='text-xs font-medium text-foreground dark:text-muted-foreground'>
                                                {opt.label}
                                            </span>
                                            <CheckDot />
                                        </div>
                                    </label>
                                ))}
                            </div>
                        </section>

                        {/* Direction */}
                        <section className='border-t border-border px-5 py-4 dark:border-card'>
                            <h6 className='mb-1 text-sm font-bold text-foreground'>Direction</h6>
                            <p className='mb-3 text-xs text-muted-foreground'>Document text direction.</p>
                            <div className='grid grid-cols-2 gap-1 rounded-lg bg-muted p-1 dark:bg-card'>
                                {(['ltr', 'rtl'] as const).map(dir => (
                                    <SegmentedOption
                                        key={dir}
                                        name='direction'
                                        value={dir}
                                        checked={settings.rtlClass === dir}
                                        label={dir}
                                        uppercase
                                        onChange={() => update({ rtlClass: dir })}
                                    />
                                ))}
                            </div>
                        </section>

                        {/* Navbar */}
                        <section className='border-t border-border px-5 py-4 dark:border-card'>
                            <h6 className='mb-1 text-sm font-bold text-foreground'>Navbar</h6>
                            <p className='mb-3 text-xs text-muted-foreground'>Top bar behavior on scroll.</p>
                            <div className='grid grid-cols-3 gap-1 rounded-lg bg-muted p-1 dark:bg-card'>
                                {navbarOptions.map(opt => (
                                    <SegmentedOption
                                        key={opt.value}
                                        name='navbar'
                                        value={opt.value}
                                        checked={settings.navbar === opt.value}
                                        label={opt.label}
                                        onChange={() => update({ navbar: opt.value })}
                                    />
                                ))}
                            </div>
                        </section>

                        {/* Footer */}
                        <section className='border-t border-border px-5 py-4 dark:border-card'>
                            <h6 className='mb-1 text-sm font-bold text-foreground'>Footer</h6>
                            <p className='mb-3 text-xs text-muted-foreground'>Footer positioning.</p>
                            <div className='grid grid-cols-3 gap-1 rounded-lg bg-muted p-1 dark:bg-card'>
                                {footerOptions.map(opt => (
                                    <SegmentedOption
                                        key={opt.value}
                                        name='footer'
                                        value={opt.value}
                                        checked={settings.footer === opt.value}
                                        label={opt.label}
                                        onChange={() => update({ footer: opt.value })}
                                    />
                                ))}
                            </div>
                        </section>
                    </div>

                    {/* Footer: Reset */}
                    <div className='border-t border-border p-4 dark:border-card'>
                        <button
                            type='button'
                            className='flex w-full items-center justify-center gap-2 rounded-lg border border-border px-4 py-2.5 text-sm font-semibold text-foreground transition-colors hover:border-primary hover:bg-primary-50 hover:text-primary dark:border-card dark:text-muted-foreground dark:hover:bg-card'
                            onClick={resetTheme}>
                            <span className='icon-[mdi--restore] size-4'></span>
                            Reset to defaults
                        </button>
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    );
}
