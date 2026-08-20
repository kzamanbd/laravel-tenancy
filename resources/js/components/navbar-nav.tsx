import { Link, usePage } from '@inertiajs/react';
import AppLogo from '@/components/app-logo';
import Breadcrumbs from '@/components/breadcrumbs';
import HorizontalMenu from '@/components/horizontal-menu';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { useAuth } from '@/context/auth-context';
import { useTheme } from '@/context/theme-context';
import { useUi } from '@/context/ui-context';

export default function NavbarNav() {
    const { setSearchOpen, setActivityOpen, setCustomizerOpen } = useUi();
    const { logout } = useAuth();
    const appName = usePage<{ name: string }>().props.name;
    const { toggleSidebar, navbarClass, settings, update } = useTheme();

    const isDark = settings.theme === 'dark';

    return (
        <header className={navbarClass}>
            {/* sidebar toggle button */}
            <div className='flex items-center gap-1.5'>
                <button
                    type='button'
                    className='flex dark:text-muted-foreground horizontal:lg:hidden! collapsed-menu:hidden'
                    aria-label='Menu'
                    onClick={toggleSidebar}>
                    <svg xmlns='http://www.w3.org/2000/svg' className='size-5' viewBox='0 0 24 24'>
                        <path d='M0 0h24v24H0z' fill='none' />
                        <path
                            fill='none'
                            stroke='currentColor'
                            strokeLinecap='round'
                            strokeLinejoin='round'
                            strokeWidth='1.5'
                            d='M9 3.5v17M3 9.4c0-2.24 0-3.36.436-4.216a4 4 0 0 1 1.748-1.748C6.04 3 7.16 3 9.4 3h5.2c2.24 0 3.36 0 4.216.436a4 4 0 0 1 1.748 1.748C21 6.04 21 7.16 21 9.4v5.2c0 2.24 0 3.36-.436 4.216a4 4 0 0 1-1.748 1.748C17.96 21 16.84 21 14.6 21H9.4c-2.24 0-3.36 0-4.216-.436a4 4 0 0 1-1.748-1.748C3 17.96 3 16.84 3 14.6z'
                        />
                    </svg>
                </button>
                <Breadcrumbs className='horizontal:hidden' />
                <a href='/dashboard' className='hidden items-center lg:flex vertical:hidden'>
                    <AppLogo className='tw-logo-icon' />
                    <div className='app-name'>{appName}</div>
                </a>
                <HorizontalMenu />
            </div>
            {/* Actions */}
            <div className='flex items-center gap-1.5'>
                <button
                    type='button'
                    className='header-icon search'
                    aria-label='Search'
                    onClick={() => setSearchOpen(true)}>
                    <span className='icon-[mdi--search]' />
                </button>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button type='button' className='header-icon help' aria-label='Help'>
                            <svg
                                className='size-4 shrink-0'
                                xmlns='http://www.w3.org/2000/svg'
                                width='24'
                                height='24'
                                viewBox='0 0 24 24'
                                fill='none'
                                stroke='currentColor'
                                strokeWidth='2'
                                strokeLinecap='round'
                                strokeLinejoin='round'>
                                <circle cx='12' cy='12' r='10' />
                                <path d='M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3' />
                                <path d='M12 17h.01' />
                            </svg>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align='end' className='w-60 text-sm'>
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--help-circle-outline] size-4' />
                                Help Center
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--user-multiple-outline] size-4' />
                                Community
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--bell-outline] size-4' />
                                What's New
                                <Badge className='ms-1'>v2.0</Badge>
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--help-circle-outline] size-4' />
                                Privacy and Legal
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--file-document-outline] size-4' />
                                Documentation
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        {/* Submit feedback */}
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--comment-text-outline] size-4' />
                                Submit Feedback
                            </a>
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <a className='dropdown-item' href='#'>
                                <span className='icon-[mdi--bug-outline] size-4' />
                                Report a Bug
                            </a>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
                {/* Activity */}
                <button
                    type='button'
                    className='header-icon'
                    aria-label={'Recent activity'}
                    onClick={() => setActivityOpen(true)}>
                    <svg
                        className='size-4 shrink-0'
                        xmlns='http://www.w3.org/2000/svg'
                        width='24'
                        height='24'
                        viewBox='0 0 24 24'
                        fill='none'
                        stroke='currentColor'
                        strokeWidth='2'
                        strokeLinecap='round'
                        strokeLinejoin='round'>
                        <path d='M22 12h-4l-3 9L9 3l-3 9H2' />
                    </svg>
                </button>
                {/* Notification */}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button type='button' className='header-icon' aria-label={`Notifications, ${5} unread`}>
                            <svg
                                className='size-4 shrink-0'
                                xmlns='http://www.w3.org/2000/svg'
                                width='24'
                                height='24'
                                viewBox='0 0 24 24'
                                fill='none'
                                stroke='currentColor'
                                strokeWidth='2'
                                strokeLinecap='round'
                                strokeLinejoin='round'>
                                <path d='M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9' />
                                <path d='M10.3 21a1.94 1.94 0 0 0 3.4 0' />
                            </svg>
                            <span className='tw-bell-icon count' aria-hidden='true'>
                                <span className='absolute inline-flex h-full w-full animate-ping rounded-full bg-danger opacity-75' />
                                <span className='tw-badge-dot bg-danger'>5</span>
                            </span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align='end' className='p-0 md:w-80'>
                        <Tabs defaultValue='all' className='gap-0'>
                            <div className='flex items-center justify-between border-b px-4 py-2 text-sm'>
                                <TabsList className='h-auto bg-transparent p-0'>
                                    <TabsTrigger value='all' className='notify-nav-item'>
                                        All
                                    </TabsTrigger>
                                    <TabsTrigger value='archived' className='notify-nav-item'>
                                        Archived
                                    </TabsTrigger>
                                </TabsList>
                                <button
                                    type='button'
                                    className='dark:text-muted-foreground'
                                    aria-label={'Notification settings'}>
                                    <span className='icon-[mdi--settings-outline] size-4' />
                                </button>
                            </div>
                            <TabsContent value='all' className='space-y-1 p-1'>
                                <div className='dropdown-item mt-2'>
                                    <div className='flex gap-3'>
                                        <div className='flex-none'>
                                            <img
                                                loading='lazy'
                                                decoding='async'
                                                src='https://ui-avatars.com/api/?name=J&background=edf1ff'
                                                alt='user'
                                                className='size-8 rounded-full object-cover'
                                            />
                                        </div>
                                        <div className='flex-1'>
                                            <div className='text-sm'>Your order is placed</div>
                                            <div className='text-xs leading-4'>
                                                Amet minim mollit non deser unt ullamco est sit aliqua.
                                            </div>
                                            <div className='mt-1 text-xs'>3 min ago</div>
                                        </div>
                                    </div>
                                </div>
                                <div className='dropdown-item'>
                                    <div className='flex gap-3'>
                                        <div className='flex-none'>
                                            <img
                                                loading='lazy'
                                                decoding='async'
                                                src='https://ui-avatars.com/api/?name=B&background=edf1ff'
                                                alt='user'
                                                className='size-8 rounded-full object-cover'
                                            />
                                        </div>
                                        <div className='flex-1'>
                                            <div className='text-sm'>Congratulations Darlene 🎉</div>
                                            <div className='text-xs leading-4'>Won the monthly best seller badge</div>
                                            <div className='mt-1 text-xs'>3 min ago</div>
                                        </div>
                                        <div className='flex-0'>
                                            <span className='inline-block size-2 rounded-full bg-red-500' />
                                        </div>
                                    </div>
                                </div>
                                <div className='dropdown-item'>
                                    <div className='flex gap-3'>
                                        <div className='flex-none'>
                                            <img
                                                loading='lazy'
                                                decoding='async'
                                                src='https://ui-avatars.com/api/?name=C&background=edf1ff'
                                                alt='user'
                                                className='size-8 rounded-full object-cover'
                                            />
                                        </div>
                                        <div className='flex-1'>
                                            <div className='text-sm'>Revised Order 👋</div>
                                            <div className='text-xs leading-4'>Won the monthly best seller badge</div>
                                            <div className='mt-1 text-xs'>3 min ago</div>
                                        </div>
                                    </div>
                                </div>
                                <div className='dropdown-item'>
                                    <div className='flex gap-3'>
                                        <div className='flex-none'>
                                            <img
                                                loading='lazy'
                                                decoding='async'
                                                src='https://ui-avatars.com/api/?name=D&background=edf1ff'
                                                alt='user'
                                                className='size-8 rounded-full object-cover'
                                            />
                                        </div>
                                        <div className='flex-1'>
                                            <div className='text-sm'>Brooklyn Simmons</div>
                                            <div className='text-xs leading-4'>
                                                Added you to Top Secret Project group...
                                            </div>
                                            <div className='mt-1 text-xs'>3 min ago</div>
                                        </div>
                                    </div>
                                </div>
                                {/* View All */}
                                <div className='dropdown-item justify-center'>
                                    <a href='#' className='flex items-center justify-center gap-2'>
                                        <svg
                                            className='size-4 shrink-0'
                                            xmlns='http://www.w3.org/2000/svg'
                                            width='24'
                                            height='24'
                                            viewBox='0 0 24 24'
                                            fill='none'
                                            stroke='currentColor'
                                            strokeWidth='2'
                                            strokeLinecap='round'
                                            strokeLinejoin='round'>
                                            <path d='M18 6 7 17l-5-5' />
                                            <path d='m22 10-7.5 7.5L13 16' />
                                        </svg>
                                        Mark all as read
                                    </a>
                                </div>
                            </TabsContent>
                            <TabsContent value='archived'>
                                {/* no notification found */}
                                <div className='flex min-h-80 flex-col items-center justify-center'>
                                    <div className='p-6 text-center'>
                                        <svg
                                            className='mx-auto mb-4 w-48'
                                            width='178'
                                            height='90'
                                            viewBox='0 0 178 90'
                                            fill='none'
                                            xmlns='http://www.w3.org/2000/svg'>
                                            <rect
                                                x='27'
                                                y='50.5'
                                                width='124'
                                                height='39'
                                                rx='7.5'
                                                fill='currentColor'
                                                className='fill-white dark:fill-neutral-800'
                                            />
                                            <rect
                                                x='27'
                                                y='50.5'
                                                width='124'
                                                height='39'
                                                rx='7.5'
                                                stroke='currentColor'
                                                className='stroke-gray-50 dark:stroke-neutral-700/10'
                                            />
                                            <rect
                                                x='34.5'
                                                y='58'
                                                width='24'
                                                height='24'
                                                rx='4'
                                                fill='currentColor'
                                                className='fill-gray-50 dark:fill-neutral-700/30'
                                            />
                                            <rect
                                                x='66.5'
                                                y='61'
                                                width='60'
                                                height='6'
                                                rx='3'
                                                fill='currentColor'
                                                className='fill-gray-50 dark:fill-neutral-700/30'
                                            />
                                            <rect
                                                x='66.5'
                                                y='73'
                                                width='77'
                                                height='6'
                                                rx='3'
                                                fill='currentColor'
                                                className='fill-gray-50 dark:fill-neutral-700/30'
                                            />
                                            <rect
                                                x='19.5'
                                                y='28.5'
                                                width='139'
                                                height='39'
                                                rx='7.5'
                                                fill='currentColor'
                                                className='fill-white dark:fill-neutral-800'
                                            />
                                            <rect
                                                x='19.5'
                                                y='28.5'
                                                width='139'
                                                height='39'
                                                rx='7.5'
                                                stroke='currentColor'
                                                className='stroke-gray-100 dark:stroke-neutral-700/30'
                                            />
                                            <rect
                                                x='27'
                                                y='36'
                                                width='24'
                                                height='24'
                                                rx='4'
                                                fill='currentColor'
                                                className='fill-gray-100 dark:fill-neutral-700/70'
                                            />
                                            <rect
                                                x='59'
                                                y='39'
                                                width='60'
                                                height='6'
                                                rx='3'
                                                fill='currentColor'
                                                className='fill-gray-100 dark:fill-neutral-700/70'
                                            />
                                            <rect
                                                x='59'
                                                y='51'
                                                width='92'
                                                height='6'
                                                rx='3'
                                                fill='currentColor'
                                                className='fill-gray-100 dark:fill-neutral-700/70'
                                            />
                                            <g filter='url(#filter15)'>
                                                <rect
                                                    x='12'
                                                    y='6'
                                                    width='154'
                                                    height='40'
                                                    rx='8'
                                                    fill='currentColor'
                                                    className='fill-white dark:fill-neutral-800'
                                                    shapeRendering='crispEdges'
                                                />
                                                <rect
                                                    x='12.5'
                                                    y='6.5'
                                                    width='153'
                                                    height='39'
                                                    rx='7.5'
                                                    stroke='currentColor'
                                                    className='stroke-gray-100 dark:stroke-neutral-700/60'
                                                    shapeRendering='crispEdges'
                                                />
                                                <rect
                                                    x='20'
                                                    y='14'
                                                    width='24'
                                                    height='24'
                                                    rx='4'
                                                    fill='currentColor'
                                                    className='fill-gray-200 dark:fill-neutral-700'
                                                />
                                                <rect
                                                    x='52'
                                                    y='17'
                                                    width='60'
                                                    height='6'
                                                    rx='3'
                                                    fill='currentColor'
                                                    className='fill-gray-200 dark:fill-neutral-700'
                                                />
                                                <rect
                                                    x='52'
                                                    y='29'
                                                    width='106'
                                                    height='6'
                                                    rx='3'
                                                    fill='currentColor'
                                                    className='fill-gray-200 dark:fill-neutral-700'
                                                />
                                            </g>
                                            <defs>
                                                <filter
                                                    id='filter15'
                                                    x='0'
                                                    y='0'
                                                    width='178'
                                                    height='64'
                                                    filterUnits='userSpaceOnUse'
                                                    colorInterpolationFilters='sRGB'>
                                                    <feFlood floodOpacity='0' result='BackgroundImageFix' />
                                                    <feColorMatrix
                                                        in='SourceAlpha'
                                                        type='matrix'
                                                        values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
                                                        result='hardAlpha'
                                                    />
                                                    <feOffset dy='6' />
                                                    <feGaussianBlur stdDeviation='6' />
                                                    <feComposite in2='hardAlpha' operator='out' />
                                                    <feColorMatrix
                                                        type='matrix'
                                                        values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.03 0'
                                                    />
                                                    <feBlend
                                                        mode='normal'
                                                        in2='BackgroundImageFix'
                                                        result='effect1_dropShadow_1187_14810'
                                                    />
                                                    <feBlend
                                                        mode='normal'
                                                        in='SourceGraphic'
                                                        in2='effect1_dropShadow_1187_14810'
                                                        result='shape'
                                                    />
                                                </filter>
                                            </defs>
                                        </svg>
                                        <div className='mt-6 font-medium text-foreground'>
                                            No archived notifications
                                        </div>
                                        <div className='text-xs text-muted-foreground'>
                                            No data here yet. We will notify you when there's an update.
                                        </div>
                                        <Button variant='light' className='mx-auto mt-4'>
                                            Notification Settings
                                        </Button>
                                    </div>
                                </div>
                            </TabsContent>
                        </Tabs>
                    </DropdownMenuContent>
                </DropdownMenu>
                {/* User */}
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <button type='button' className='header-icon p-0' aria-label={'User menu'}>
                            <img
                                loading='lazy'
                                decoding='async'
                                className='user-avatar'
                                src='https://ui-avatars.com/api/?name=DraftScripts&background=FF8A3D&color=ffffff'
                                alt='User avatar'
                            />
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent asChild align='end' className='w-64 p-1'>
                        <ul>
                            <li className='user-menu-item'>
                                <a href='#' className='user-menu-link mb-2'>
                                    <div className='shrink-0'>
                                        <img
                                            loading='lazy'
                                            decoding='async'
                                            className='user-avatar'
                                            src='https://ui-avatars.com/api/?name=DraftScripts&background=FF8A3D&color=ffffff'
                                            alt='User avatar'
                                        />
                                    </div>
                                    <div className='px-1'>
                                        <p className='leading-5 font-medium text-foreground dark:text-muted-foreground'>
                                            DraftScripts
                                        </p>
                                        <p className='text-xs leading-5 text-muted-foreground'>Software Engineer</p>
                                    </div>
                                </a>
                            </li>
                            <li className='item-group-divider' />
                            <li className='user-menu-item'>
                                <Link href='/account/billing' className='user-menu-link'>
                                    <span className='icon-[mdi--wallet] size-4' />
                                    <span>Billing</span>
                                </Link>
                            </li>
                            <li className='user-menu-item'>
                                <Link href='/account/overview' className='user-menu-link'>
                                    <span className='icon-[mdi--cog] size-4' />
                                    <span>Settings</span>
                                </Link>
                            </li>
                            <li className='user-menu-item'>
                                <a href='#' className='user-menu-link'>
                                    <span className='icon-[mdi--user] size-4' />
                                    <span>My Account</span>
                                </a>
                            </li>
                            <li className='item-group-divider' />
                            <li className='user-menu-item'>
                                <label className='user-menu-link mb-0 justify-between font-normal'>
                                    <span>Dark Mode</span>
                                    <div className='relative h-5 w-10'>
                                        <input
                                            id='custom_switch_checkbox4'
                                            type='checkbox'
                                            checked={isDark}
                                            onChange={e => update({ theme: e.target.checked ? 'dark' : 'system' })}
                                            className='theme-switcher peer absolute z-10 h-full w-full cursor-pointer opacity-0'
                                        />
                                        <span className='block h-full rounded-full bg-muted peer-checked:bg-primary before:absolute before:bottom-1 before:left-1 before:h-3 before:w-3 before:rounded-full before:bg-card before:transition-all before:duration-300 peer-checked:before:left-6 dark:bg-card dark:peer-checked:before:bg-card' />
                                    </div>
                                </label>
                            </li>
                            <li className='item-group-divider' />
                            <li className='user-menu-item'>
                                <button
                                    type='button'
                                    className='user-menu-link justify-between'
                                    onClick={() => setCustomizerOpen(true)}>
                                    Customization
                                    <Badge variant='soft-primary' pill>
                                        New
                                    </Badge>
                                </button>
                            </li>
                            <li className='user-menu-item'>
                                <a href='#' className='user-menu-link'>
                                    My Subscription
                                </a>
                            </li>
                            <li className='user-menu-item'>
                                <button className='user-menu-link' onClick={logout}>
                                    Sign out
                                </button>
                            </li>
                        </ul>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
