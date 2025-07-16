<header class="navbar-nav">
    <div class="primary-header">
        <!-- sidebar toggle button -->
        <div class="left-header-items">
            <button type="button" aria-label="Menu">
                <svg class="hidden-sidebar" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                        <path
                            d="M2 11c0-3.771 0-5.657 1.172-6.828S6.229 3 10 3h4c3.771 0 5.657 0 6.828 1.172S22 7.229 22 11v2c0 3.771 0 5.657-1.172 6.828S17.771 21 14 21h-4c-3.771 0-5.657 0-6.828-1.172S2 16.771 2 13z" />
                        <path stroke-linecap="round" d="M15 21V3" />
                    </g>
                </svg>
                <svg class="toggle-sidebar lg:hidden" stroke="currentColor" fill="none" stroke-width="0"
                    viewBox="0 0 24 24" height="22" width="22" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16">
                    </path>
                </svg>
            </button>
            <a href="/index.html" class="horizontal-logo">
                <svg class="tw-logo" width="100%" height="100%" viewBox="0 0 59 38" fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path class="fill-primary"
                        d="M11.7204 0.398132C17.2432 0.398132 21.7204 4.87529 21.7204 10.3981V27.8981C21.7204 28.3584 22.0935 28.7315 22.5537 28.7315C23.0139 28.7315 23.387 28.3584 23.387 27.8981V10.3981C23.387 4.87528 27.8642 0.398132 33.387 0.398132H46.7204C53.1637 0.398132 58.387 5.62148 58.387 12.0648V16.2445C58.387 19.0514 57.3754 21.7649 55.5371 23.8861L44.1146 37.0648H30.8822L47.9801 17.3366L48.0729 17.2194C48.2767 16.9367 48.387 16.5955 48.387 16.2445V12.0648C48.387 11.1443 47.6409 10.3981 46.7204 10.3981H35.0537V9.5648C35.0537 9.10456 34.6806 8.73147 34.2204 8.73147C33.7601 8.73147 33.387 9.10456 33.387 9.5648V27.0648C33.387 32.5876 28.9099 37.0648 23.387 37.0648H21.7204C16.1975 37.0648 11.7204 32.5876 11.7204 27.0648V9.5648C11.7204 9.10456 11.3473 8.73147 10.887 8.73147C10.4268 8.73147 10.0537 9.10456 10.0537 9.5648V37.0648H0.0537109V10.3981C0.0537109 4.87528 4.53086 0.398132 10.0537 0.398132H11.7204Z">
                    </path>
                </svg>
                <div class="app-name">Metricon</div>
            </a>
        </div>
        <!-- Search  -->
        <div class="quick-search-box" data-hs-overlay="#global-search">
            <input id="gs-input" type="text" class="quick-search-input" placeholder="Quick search..." />
            <div class="quick-search-command">
                <span class="icon-[mdi--apple-keyboard-command] size-3"></span>
                <span class="icon-[mdi--add] size-3"></span>
                <span class="text-xs">K</span>
            </div>
            <button type="button" class="quick-search-btn">
                <span class="icon-[mdi--search]"></span>
            </button>
        </div>
        <!-- Actions -->
        <div class="right-header-items">
            <div class="hs-dropdown inline-flex [--placement:bottom-right]">
                <button type="button" class="header-icon help" aria-label="Help">
                    <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <path d="M12 17h.01"></path>
                    </svg>
                </button>
                <div class="hs-dropdown-menu dropdown-menu w-60 text-sm">
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--help-circle-outline] size-4"></span>
                        Help Center
                    </a>
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--user-multiple-outline] size-4"></span>
                        Community
                    </a>
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--bell-outline] size-4"></span>
                        What's New
                        <span class="badge bg-primary text-sx ms-1">v2.0</span>
                    </a>
                    <div class="item-group-divider"></div>
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--help-circle-outline] size-4"></span>
                        Privacy and Legal
                    </a>
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--file-document-outline] size-4"></span>
                        Documentation
                    </a>
                    <div class="item-group-divider"></div>
                    <!-- Submit feedback -->
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--comment-text-outline] size-4"></span>
                        Submit Feedback
                    </a>
                    <a class="dropdown-item" href="#">
                        <span class="icon-[mdi--bug-outline] size-4"></span>
                        Report a Bug
                    </a>
                </div>
            </div>
            <!-- Activity -->
            <div class="hs-dropdown inline-flex [--placement:bottom-right]">
                <button type="button" class="header-icon activity" aria-label="Activity">
                    <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                    </svg>
                </button>
            </div>
            <!-- Notification -->
            <div class="hs-dropdown inline-flex [--auto-close:inside] [--placement:bottom-right]">
                <button type="button" class="header-icon hs-dropdown-toggle" aria-label="Notification">
                    <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                    </svg>
                    <span class="tw-bell-icon count">
                        <span class="tw-icon-badge bg-danger"></span>
                        <span class="tw-badge-dot bg-danger">5</span>
                    </span>
                </button>
            </div>
            <!-- User -->
            <div class="hs-dropdown user-dropdown [--auto-close:inside]">
                <button type="button" class="hs-dropdown-toggle header-icon p-0">
                    <img class="user-avatar" src="/images/author.png" alt="Author" />
                </button>

                <ul class="hs-dropdown-menu dropdown-menu">
                    <li class="user-menu-item">
                        <a href="#" class="user-menu-link mb-2">
                            <div class="shrink-0">
                                <img class="user-avatar" src="/images/author.png" alt="Author" />
                            </div>
                            <div class="px-1">
                                <p class="leading-5 font-medium text-gray-900 dark:text-gray-300">
                                    Kamruzzaman
                                </p>
                                <p class="text-xs leading-5 text-gray-500 dark:text-gray-300">
                                    Software Engineer
                                </p>
                            </div>
                        </a>
                    </li>
                    <li class="item-group-divider"></li>
                    <li class="user-menu-item">
                        <a href="#" class="user-menu-link">
                            <span class="icon-[mdi--wallet] size-4"></span>
                            <span>Billing</span>
                        </a>
                    </li>
                    <li class="user-menu-item">
                        <a href="#" class="user-menu-link">
                            <span class="icon-[mdi--cog] size-4"></span>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li class="user-menu-item">
                        <a href="#" class="user-menu-link">
                            <span class="icon-[mdi--user] size-4"></span>
                            <span>My Account</span>
                        </a>
                    </li>
                    <li class="item-group-divider"></li>
                    <li class="user-menu-item">
                        <label class="user-menu-link mb-0 justify-between font-normal">
                            <span>Dark Mode</span>
                            <div class="relative h-5 w-10">
                                <input type="checkbox" value="toggle"
                                    class="theme-switcher peer absolute z-10 h-full w-full cursor-pointer opacity-0"
                                    id="custom_switch_checkbox4" />
                                <span
                                    class="peer-checked:bg-primary block h-full rounded-full bg-gray-200 before:absolute before:bottom-1 before:left-1 before:h-3 before:w-3 before:rounded-full before:bg-white before:transition-all before:duration-300 peer-checked:before:left-6 dark:bg-gray-700 dark:peer-checked:before:bg-white"></span>
                            </div>
                        </label>
                    </li>
                    <li class="item-group-divider"></li>
                    <li class="user-menu-item">
                        <a href="#" data-hs-overlay="#customizer-drawer"
                            class="user-menu-link justify-between">
                            Customization
                            <span class="badge badge-soft-primary badge-pill"> New </span>
                        </a>
                    </li>
                    <li class="user-menu-item">
                        <a href="#" class="user-menu-link"> My Subscription </a>
                    </li>
                    <li class="user-menu-item">
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="user-menu-link w-full text-left"> Sign out </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
