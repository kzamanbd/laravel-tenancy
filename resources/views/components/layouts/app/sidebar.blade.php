<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <div class="tw--wrapper vertical">
            <!-- Loading dialog -->
            <div class="loading">
                <div class="loading-box">
                    <div class="loading-box1"></div>
                    <div class="loading-box2"></div>
                </div>
            </div>

            <!-- menu shadow -->
            <div class="menu-shadow hidden"></div>

            <!-- start vertical-menu -->
            <x-vertical-menu />
            <!-- end vertical-menu -->

            <!-- start main content -->
            <div class="tw--container">
                <x-navbar />

                <main class="tw--content animate__animated">
                    <!-- Start Breadcrumb -->
                    <nav class="breadcrumb">
                        <div class="left-link">
                            <h5 class="breadcrumb-title">Default</h5>
                        </div>
                        <ol class="breadcrumb-nav">
                            <li class="breadcrumb-item">
                                <a href="/"> Dashboard </a>
                            </li>
                            <li class="breadcrumb-item current" aria-current="page">Default</li>
                        </ol>
                    </nav>
                    <!-- End Breadcrumb -->
                    {{ $slot }}
                </main>

                <x-footer />
            </div>

            <!-- Start search box -->
            <div id="global-search" class="hs-overlay tw-modal hidden">
                <div class="tw-modal-dialog lg:my-24">
                    <div class="tw-modal-content">
                        <header class="tw-modal-header py-1.5">
                            <div class="flex w-full items-center justify-around">
                                <svg class="quick-search-svg" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.3-4.3"></path>
                                </svg>
                                <input type="text" autofocus id="s-box-input" class="search-box-input"
                                    placeholder="Search or type a command" />
                                <button type="button" class="tw-modal-close" data-hs-overlay="#global-search">
                                    <i class="icon-[mdi--close]"></i>
                                </button>
                            </div>
                        </header>
                        <main data-simplebar class="tw-modal-body global-search-container">
                            <div class="topics-search px-2 dark:text-gray-300">
                                <p class="mb-2">Topics</p>
                                <div class="flex flex-wrap gap-4">
                                    <a class="btn btn-light btn-sm rounded-full" href="#">
                                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                                            <path
                                                d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z">
                                            </path>
                                        </svg>
                                        Inbox
                                    </a>
                                    <a class="btn btn-light btn-sm rounded-full" href="#">
                                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                                        </svg>
                                        Activity
                                    </a>
                                    <a class="btn btn-light btn-sm rounded-full" href="#">
                                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="16"
                                            height="16" fill="currentColor" viewBox="0 0 16 16">
                                            <path
                                                d="M14.9636 7.95706C14.9636 7.03203 13.8052 6.1554 12.0292 5.61175C12.439 3.80157 12.2569 2.36137 11.4542 1.90027C11.2692 1.79212 11.0529 1.74089 10.8167 1.74089V2.3756C10.9476 2.3756 11.0529 2.40121 11.1411 2.4496C11.5282 2.67161 11.6961 3.51695 11.5652 4.60419C11.5339 4.87174 11.4827 5.15351 11.4201 5.44099C10.8622 5.30438 10.2531 5.19907 9.61271 5.13076C9.22848 4.60419 8.82999 4.12602 8.42868 3.70764C9.35654 2.84523 10.2275 2.37275 10.8195 2.37275V1.73804C10.0368 1.73804 9.01216 2.2959 7.97612 3.26362C6.9401 2.30159 5.91546 1.74942 5.13274 1.74942V2.38413C5.72192 2.38413 6.59569 2.85376 7.52358 3.71049C7.12509 4.12887 6.72663 4.60419 6.34807 5.13076C5.70484 5.19907 5.09574 5.30438 4.53786 5.44384C4.47241 5.15921 4.42403 4.88314 4.38988 4.61842C4.25609 3.53117 4.42118 2.68584 4.80541 2.46098C4.89079 2.40975 5.0018 2.38698 5.13274 2.38698V1.75227C4.89364 1.75227 4.67732 1.8035 4.48948 1.91166C3.68969 2.37275 3.51038 3.81009 3.92308 5.6146C2.15272 6.16108 1 7.03488 1 7.95706C1 8.88209 2.15842 9.75872 3.93446 10.3024C3.52461 12.1126 3.70677 13.5528 4.50941 14.0138C4.69443 14.122 4.91072 14.1732 5.14982 14.1732C5.93254 14.1732 6.95718 13.6154 7.99319 12.6477C9.02924 13.6097 10.0539 14.1619 10.8366 14.1619C11.0757 14.1619 11.292 14.1106 11.4799 14.0025C12.2796 13.5414 12.4589 12.104 12.0463 10.2995C13.8109 9.7559 14.9636 8.87924 14.9636 7.95706ZM11.2578 6.05862C11.1525 6.42577 11.0216 6.80434 10.8736 7.1829C10.7569 6.9552 10.6345 6.72748 10.5007 6.49978C10.3698 6.27209 10.2303 6.0501 10.0909 5.83378C10.495 5.89356 10.885 5.96753 11.2578 6.05862ZM9.95427 9.08986C9.73225 9.4741 9.50455 9.83843 9.2683 10.1771C8.84422 10.2141 8.41446 10.2341 7.98182 10.2341C7.55203 10.2341 7.12227 10.2141 6.701 10.18C6.46479 9.84128 6.23424 9.4798 6.01222 9.09841C5.7959 8.72556 5.59951 8.347 5.42022 7.96561C5.59668 7.5842 5.7959 7.20282 6.00937 6.82996C6.23139 6.4457 6.45908 6.0814 6.69533 5.74269C7.11942 5.70569 7.54918 5.68576 7.98182 5.68576C8.41161 5.68576 8.84137 5.70569 9.26263 5.73984C9.49885 6.07855 9.7294 6.44003 9.95142 6.82141C10.1677 7.19427 10.3641 7.57283 10.5434 7.95421C10.3641 8.33562 10.1677 8.717 9.95427 9.08986ZM10.8736 8.71986C11.0273 9.10127 11.1582 9.48265 11.2664 9.85266C10.8935 9.94374 10.5007 10.0206 10.0937 10.0803C10.2332 9.86121 10.3727 9.63633 10.5036 9.40579C10.6345 9.17809 10.7569 8.94755 10.8736 8.71986ZM7.98752 11.7568C7.72282 11.4835 7.45812 11.179 7.19625 10.846C7.45242 10.8574 7.71427 10.8659 7.97897 10.8659C8.24652 10.8659 8.51121 10.8602 8.77024 10.846C8.51406 11.179 8.24937 11.4835 7.98752 11.7568ZM5.8699 10.0803C5.46575 10.0206 5.07581 9.94659 4.70295 9.85551C4.80826 9.48835 4.9392 9.10979 5.08719 8.73123C5.2039 8.95895 5.32628 9.18665 5.46004 9.41434C5.59383 9.64204 5.73044 9.86403 5.8699 10.0803ZM7.97329 4.15735C8.23799 4.43058 8.50269 4.73513 8.76454 5.06813C8.50836 5.05676 8.24652 5.0482 7.98182 5.0482C7.71427 5.0482 7.44957 5.05391 7.19057 5.06813C7.44672 4.73513 7.71142 4.43058 7.97329 4.15735ZM5.86705 5.83378C5.72759 6.05295 5.58813 6.27779 5.45719 6.50834C5.32628 6.73603 5.2039 6.96372 5.08719 7.19142C4.9335 6.81004 4.80256 6.42863 4.69443 6.05862C5.06726 5.97039 5.46004 5.89356 5.86705 5.83378ZM3.29122 9.39727C2.28365 8.96748 1.63186 8.40393 1.63186 7.95706C1.63186 7.5102 2.28365 6.9438 3.29122 6.51686C3.536 6.41155 3.80354 6.31764 4.07962 6.22941C4.24186 6.78726 4.45533 7.36788 4.72003 7.96276C4.45818 8.55476 4.24756 9.13257 4.08817 9.68756C3.80639 9.59933 3.53884 9.50257 3.29122 9.39727ZM4.82249 13.4645C4.4354 13.2425 4.26749 12.3972 4.3984 11.3099C4.4297 11.0424 4.48096 10.7606 4.54356 10.4731C5.10144 10.6097 5.71052 10.7151 6.35092 10.7834C6.73516 11.3099 7.13364 11.7881 7.53495 12.2065C6.6071 13.0689 5.73615 13.5414 5.14412 13.5414C5.01603 13.5385 4.90787 13.5129 4.82249 13.4645ZM11.5738 11.2957C11.7075 12.383 11.5425 13.2283 11.1582 13.4531C11.0728 13.5044 10.9618 13.5272 10.8309 13.5272C10.2417 13.5272 9.36794 13.0575 8.44006 12.2008C8.83854 11.7824 9.237 11.3071 9.61556 10.7805C10.2588 10.7122 10.8679 10.6069 11.4258 10.4674C11.4912 10.7549 11.5425 11.031 11.5738 11.2957ZM12.6696 9.39727C12.4248 9.50257 12.1572 9.59651 11.8812 9.68474C11.7189 9.12687 11.5055 8.54624 11.2408 7.95136C11.5026 7.35936 11.7132 6.78156 11.8726 6.22656C12.1544 6.31479 12.4219 6.41155 12.6724 6.51686C13.68 6.94665 14.3318 7.5102 14.3318 7.95706C14.3289 8.40393 13.6771 8.97033 12.6696 9.39727Z">
                                            </path>
                                            <path
                                                d="M7.97885 9.25778C8.69722 9.25778 9.27959 8.67543 9.27959 7.95706C9.27959 7.23869 8.69722 6.65632 7.97885 6.65632C7.26048 6.65632 6.67814 7.23869 6.67814 7.95706C6.67814 8.67543 7.26048 9.25778 7.97885 9.25778Z">
                                            </path>
                                        </svg>
                                        React
                                    </a>
                                    <a class="btn btn-light btn-sm rounded-full" href="#">
                                        <svg class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24"
                                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z">
                                            </path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" x2="8" y1="13" y2="13"></line>
                                            <line x1="16" x2="8" y1="17" y2="17"></line>
                                            <line x1="10" x2="8" y1="9" y2="9">
                                            </line>
                                        </svg>
                                        Files
                                    </a>
                                </div>
                            </div>

                            <div class="recent-search pt-3 dark:text-gray-300">
                                <p class="mb-2 px-3">Recent</p>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <svg class="size-5 shrink-0" width="33" height="32" viewBox="0 0 33 32"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_11766_122209)">
                                            <path
                                                d="M3.11931 28.4817H8.21019V16.1181L0.937439 10.6636V26.3C0.937439 27.5054 1.91381 28.4819 3.11931 28.4819V28.4817Z"
                                                fill="#4285F4"></path>
                                            <path
                                                d="M25.6647 28.4817H30.7556C31.961 28.4817 32.9374 27.5054 32.9374 26.2999V10.6636L25.6647 16.1181V28.4817Z"
                                                fill="#34A853"></path>
                                            <path
                                                d="M25.6647 6.66356V16.1181L32.9374 10.6636V7.7545C32.9374 5.05812 29.8593 3.51812 27.701 5.13631L25.6647 6.66356Z"
                                                fill="#FBBC04"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M8.21021 16.1181V6.66356L16.9375 13.2091L25.6647 6.66356V16.1181L16.9375 22.6636L8.21021 16.1181Z"
                                                fill="#EA4335"></path>
                                            <path
                                                d="M0.937439 7.7545V10.6636L8.21019 16.1181V6.66356L6.17381 5.13631C4.01556 3.51813 0.937439 5.05813 0.937439 7.75438V7.7545Z"
                                                fill="#C5221F"></path>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_11766_122209">
                                                <rect width="32" height="32" fill="white"
                                                    transform="translate(0.937439)"></rect>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                    <span class="text-sm dark:text-neutral-200"> Compose an email </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500"> Gmail </span>

                                    <div class="ml-auto flex items-center gap-1">
                                        <div class="btn btn-light btn-xs size-5">
                                            <svg class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="btn btn-light btn-xs size-5">
                                            <svg class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M3 3h6l6 18h6"></path>
                                                <path d="M14 3h7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </a>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <svg class="size-5 shrink-0" width="32" height="32" viewBox="0 0 32 32"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M11.7326 0C9.96372 0.00130479 8.53211 1.43397 8.53342 3.19935C8.53211 4.96473 9.96503 6.39739 11.7339 6.39869H14.9345V3.20065C14.9358 1.43527 13.5029 0.00260958 11.7326 0C11.7339 0 11.7339 0 11.7326 0M11.7326 8.53333H3.20053C1.43161 8.53464 -0.00130383 9.9673 3.57297e-06 11.7327C-0.00261123 13.4981 1.4303 14.9307 3.19922 14.9333H11.7326C13.5016 14.932 14.9345 13.4994 14.9332 11.734C14.9345 9.9673 13.5016 8.53464 11.7326 8.53333Z"
                                            fill="#36C5F0"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M32 11.7327C32.0013 9.9673 30.5684 8.53464 28.7995 8.53333C27.0306 8.53464 25.5976 9.9673 25.5989 11.7327V14.9333H28.7995C30.5684 14.932 32.0013 13.4994 32 11.7327ZM23.4666 11.7327V3.19935C23.4679 1.43527 22.0363 0.00260958 20.2674 0C18.4984 0.00130479 17.0655 1.43397 17.0668 3.19935V11.7327C17.0642 13.4981 18.4971 14.9307 20.2661 14.9333C22.035 14.932 23.4679 13.4994 23.4666 11.7327Z"
                                            fill="#2EB67D"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M20.2661 32C22.035 31.9987 23.4679 30.566 23.4666 28.8007C23.4679 27.0353 22.035 25.6026 20.2661 25.6013H17.0656V28.8007C17.0642 30.5647 18.4972 31.9974 20.2661 32ZM20.2661 23.4654H28.7995C30.5684 23.4641 32.0013 22.0314 32 20.266C32.0026 18.5006 30.5697 17.068 28.8008 17.0654H20.2674C18.4985 17.0667 17.0656 18.4993 17.0669 20.2647C17.0656 22.0314 18.4972 23.4641 20.2661 23.4654Z"
                                            fill="#ECB22E"></path>
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M8.93953e-07 20.266C-0.00130651 22.0314 1.43161 23.4641 3.20052 23.4654C4.96944 23.4641 6.40235 22.0314 6.40105 20.266V17.0667H3.20052C1.43161 17.068 -0.00130651 18.5006 8.93953e-07 20.266ZM8.53342 20.266V28.7993C8.5308 30.5647 9.96372 31.9974 11.7326 32C13.5016 31.9987 14.9345 30.566 14.9332 28.8007V20.2686C14.9358 18.5032 13.5029 17.0706 11.7339 17.068C9.96372 17.068 8.53211 18.5006 8.53342 20.266C8.53342 20.2673 8.53342 20.266 8.53342 20.266Z"
                                            fill="#E01E5A"></path>
                                    </svg>
                                    <span class="text-sm dark:text-neutral-200"> Start a conversation </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500"> Slack </span>

                                    <div class="ml-auto flex items-center gap-1">
                                        <div class="btn btn-light btn-xs size-5">
                                            <svg class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="btn btn-light btn-xs size-5">
                                            <svg class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="m18 15-6-6-6 6"></path>
                                            </svg>
                                        </div>
                                        <div class="btn btn-light btn-xs size-5">S</div>
                                    </div>
                                </a>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <svg class="size-5 shrink-0" width="32" height="32" viewBox="0 0 32 32"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M11.7438 0.940745C6.84695 1.30308 2.6841 1.63631 2.48837 1.67533C1.9396 1.77319 1.44038 2.14544 1.20563 2.63537L1 3.06646L1.01982 13.3407L1.04893 23.615L1.36234 24.2517C1.53886 24.6042 2.73365 26.2499 4.0362 27.9439C6.61221 31.2836 6.79802 31.47 7.77726 31.5679C8.06156 31.597 10.1966 31.4991 12.5081 31.3622C14.8295 31.2154 18.5508 30.99 20.7842 30.863C30.3233 30.2839 29.8334 30.3328 30.3815 29.8627C31.0672 29.2947 31.0183 30.2251 31.0474 17.7377C31.0672 7.15003 31.0573 6.45509 30.9006 6.13177C30.7148 5.76943 30.3815 5.51487 26.0329 2.45885C23.1243 0.421704 22.9186 0.313932 21.6155 0.294111C21.0772 0.274911 16.6307 0.568497 11.7438 0.940745ZM22.752 2.28232C23.1633 2.46814 26.1704 4.56412 26.6108 4.9661C26.7284 5.08378 26.7675 5.18164 26.7086 5.24048C26.5717 5.35817 7.96245 6.465 7.42421 6.38634C7.17956 6.34732 6.81722 6.20052 6.61159 6.06302C5.75932 5.48514 3.64413 3.75149 3.64413 3.62452C3.64413 3.29129 3.57538 3.29129 11.8714 2.69421C13.4582 2.58644 16.0633 2.39071 17.6502 2.26312C21.0871 1.98874 22.1159 1.99865 22.752 2.28232ZM28.6677 7.63996C28.8046 7.77685 28.9223 8.04132 28.9613 8.29589C28.9904 8.53125 29.0102 12.9189 28.9904 18.0313C28.9613 26.8067 28.9514 27.3555 28.7848 27.61C28.6869 27.7667 28.4912 27.9333 28.3438 27.9823C27.9331 28.1489 8.43318 29.2557 8.03183 29.138C7.84601 29.0891 7.59083 28.9324 7.45394 28.7955L7.21858 28.541L7.18947 19.0799C7.16965 12.4395 7.18947 9.5012 7.26813 9.23673C7.32697 9.041 7.47376 8.80564 7.60136 8.72759C7.77788 8.60991 8.93364 8.51205 12.9101 8.2773C15.7016 8.1206 20.0206 7.85613 22.4987 7.70933C28.3933 7.34638 28.3741 7.34638 28.6677 7.63996Z"
                                            class="bvsy9 dark:fill-neutral-200" fill="currentColor"></path>
                                        <path
                                            d="M23.4277 10.8818C22.3698 10.9506 21.4296 11.0484 21.3218 11.1073C20.9985 11.2739 20.8028 11.5483 20.7638 11.8617C20.7347 12.185 20.8325 12.224 21.8898 12.3516L22.35 12.4104V16.5925C22.35 19.0799 22.311 20.7256 22.2621 20.6767C22.2131 20.6178 20.8226 18.5027 19.167 15.9756C17.512 13.4392 16.1407 11.3525 16.1209 11.3333C16.1011 11.3135 15.024 11.3724 13.7313 11.4609C12.1445 11.5687 11.273 11.6666 11.0965 11.7644C10.8122 11.9112 10.4988 12.4303 10.4988 12.7734C10.4988 12.979 10.871 13.0868 11.6545 13.0868H12.0658V25.1139L11.4 25.3196C10.8809 25.4763 10.7044 25.5741 10.6165 25.7698C10.4598 26.1031 10.4697 26.4066 10.6264 26.4066C10.6852 26.4066 11.792 26.3378 13.0649 26.2598C15.582 26.113 15.8657 26.0442 16.1302 25.5252C16.2088 25.3685 16.277 25.2019 16.277 25.1529C16.277 25.1139 15.9345 24.9962 15.5226 24.8984C15.1014 24.8005 14.6802 24.7027 14.5923 24.6828C14.4257 24.6339 14.4157 24.3304 14.4157 20.1186V15.6033L17.3931 20.2753C20.5173 25.1721 20.9093 25.7308 21.3893 25.9755C21.987 26.2889 23.5051 26.0733 24.2688 25.5741L24.5042 25.4273L24.524 18.7479L24.5531 12.0586L25.0722 11.9608C25.6891 11.8431 25.9734 11.5594 25.9734 11.0695C25.9734 10.7561 25.9536 10.7362 25.66 10.7462C25.4847 10.7542 24.4757 10.813 23.4277 10.8818Z"
                                            class="bvsy9 dark:fill-neutral-200" fill="currentColor"></path>
                                    </svg>
                                    <span class="text-sm dark:text-neutral-200"> Create a project </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500"> Notion </span>

                                    <div class="ml-auto flex items-center gap-1">
                                        <div class="btn btn-light btn-xs size-5">
                                            <svg class="size-3 shrink-0" xmlns="http://www.w3.org/2000/svg"
                                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path
                                                    d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="btn btn-light btn-xs size-5">N</div>
                                    </div>
                                </a>
                            </div>

                            <div class="users-search pt-3 dark:text-gray-300">
                                <p class="mb-2 px-3">Users</p>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <img class="size-5 rounded-full" src="/images/users/avatar-10.png"
                                        alt="avatar" />
                                    <span> Kamruzzaman </span>
                                    <span class="text-xs text-green-600">Active now</span>
                                </a>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <img class="size-5 rounded-full" src="/images/users/avatar-12.png"
                                        alt="avatar" />
                                    <span> Chris Wood </span>
                                    <span class="text-xs text-gray-400"> Last seen 2 hours ago </span>
                                </a>
                            </div>

                            <div class="event-search pt-3 dark:text-gray-300">
                                <p class="mb-2 px-3">Events Tomorrow</p>
                                <!-- Events -->
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <div class="bg-primary h-6 w-0.5"></div>
                                    <div class="flex justify-between">
                                        <div>
                                            <div class="text-sm dark:text-neutral-200">UI/UX Design Masterclass</div>
                                            <div class="text-xs text-gray-400 dark:text-neutral-500">12 Aug, 2021</div>
                                        </div>
                                    </div>
                                </a>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <div class="h-6 w-0.5 bg-green-500"></div>
                                    <div class="flex justify-between">
                                        <div>
                                            <div class="text-sm dark:text-neutral-200">Product Management Workshop
                                            </div>
                                            <div class="text-xs text-gray-400 dark:text-neutral-500">12 Aug, 2021</div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="files-search pt-3 dark:text-gray-300">
                                <p class="mb-2 px-3">Files</p>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <svg class="size-5 shrink-0" width="32" height="32" viewBox="0 0 32 32"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M20.0324 1.91994H9.45071C9.09999 1.91994 8.76367 2.05926 8.51567 2.30725C8.26767 2.55523 8.12839 2.89158 8.12839 3.24228V8.86395L20.0324 15.8079L25.9844 18.3197L31.9364 15.8079V8.86395L20.0324 1.91994Z"
                                            fill="#21A366"></path>
                                        <path d="M8.12839 8.86395H20.0324V15.8079H8.12839V8.86395Z" fill="#107C41">
                                        </path>
                                        <path
                                            d="M30.614 1.91994H20.0324V8.86395H31.9364V3.24228C31.9364 2.89158 31.7971 2.55523 31.5491 2.30725C31.3011 2.05926 30.9647 1.91994 30.614 1.91994Z"
                                            fill="#33C481"></path>
                                        <path
                                            d="M20.0324 15.8079H8.12839V28.3736C8.12839 28.7243 8.26767 29.0607 8.51567 29.3087C8.76367 29.5567 9.09999 29.6959 9.45071 29.6959H30.6141C30.9647 29.6959 31.3011 29.5567 31.549 29.3087C31.797 29.0607 31.9364 28.7243 31.9364 28.3736V22.7519L20.0324 15.8079Z"
                                            fill="#185C37"></path>
                                        <path d="M20.0324 15.8079H31.9364V22.7519H20.0324V15.8079Z" fill="#107C41">
                                        </path>
                                        <path opacity="0.1"
                                            d="M16.7261 6.87994H8.12839V25.7279H16.7261C17.0764 25.7269 17.4121 25.5872 17.6599 25.3395C17.9077 25.0917 18.0473 24.756 18.0484 24.4056V8.20226C18.0473 7.8519 17.9077 7.51616 17.6599 7.2684C17.4121 7.02064 17.0764 6.88099 16.7261 6.87994Z"
                                            fill="black"></path>
                                        <path opacity="0.2"
                                            d="M15.7341 7.87194H8.12839V26.7199H15.7341C16.0844 26.7189 16.4201 26.5792 16.6679 26.3315C16.9157 26.0837 17.0553 25.748 17.0564 25.3976V9.19426C17.0553 8.84386 16.9157 8.50818 16.6679 8.26042C16.4201 8.01266 16.0844 7.87299 15.7341 7.87194Z"
                                            fill="black"></path>
                                        <path opacity="0.2"
                                            d="M15.7341 7.87194H8.12839V24.7359H15.7341C16.0844 24.7349 16.4201 24.5952 16.6679 24.3475C16.9157 24.0997 17.0553 23.764 17.0564 23.4136V9.19426C17.0553 8.84386 16.9157 8.50818 16.6679 8.26042C16.4201 8.01266 16.0844 7.87299 15.7341 7.87194Z"
                                            fill="black"></path>
                                        <path opacity="0.2"
                                            d="M14.7421 7.87194H8.12839V24.7359H14.7421C15.0924 24.7349 15.4281 24.5952 15.6759 24.3475C15.9237 24.0997 16.0633 23.764 16.0644 23.4136V9.19426C16.0633 8.84386 15.9237 8.50818 15.6759 8.26042C15.4281 8.01266 15.0924 7.87299 14.7421 7.87194Z"
                                            fill="black"></path>
                                        <path
                                            d="M1.51472 7.87194H14.7421C15.0927 7.87194 15.4291 8.01122 15.6771 8.25922C15.925 8.50722 16.0644 8.84354 16.0644 9.19426V22.4216C16.0644 22.7723 15.925 23.1087 15.6771 23.3567C15.4291 23.6047 15.0927 23.7439 14.7421 23.7439H1.51472C1.16402 23.7439 0.827672 23.6047 0.579686 23.3567C0.3317 23.1087 0.192383 22.7723 0.192383 22.4216V9.19426C0.192383 8.84354 0.3317 8.50722 0.579686 8.25922C0.827672 8.01122 1.16402 7.87194 1.51472 7.87194Z"
                                            fill="#107C41"></path>
                                        <path
                                            d="M3.69711 20.7679L6.90722 15.794L3.96694 10.8479H6.33286L7.93791 14.0095C8.08536 14.3091 8.18688 14.5326 8.24248 14.68H8.26328C8.36912 14.4407 8.47984 14.2079 8.5956 13.9817L10.3108 10.8479H12.4822L9.46656 15.7663L12.5586 20.7679H10.2473L8.3932 17.2959C8.30592 17.148 8.23184 16.9927 8.172 16.8317H8.14424C8.09016 16.9891 8.01824 17.1399 7.92998 17.2811L6.02236 20.7679H3.69711Z"
                                            fill="white"></path>
                                    </svg>
                                    <span class="text-sm dark:text-neutral-200"> weekly-reports.xlsx </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500"> 12 Aug, 2021 </span>
                                </a>
                                <a class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
                                    href="#">
                                    <svg class="size-5 shrink-0" width="32" height="32" viewBox="0 0 32 32"
                                        fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M30.6141 1.91994H9.45071C9.09999 1.91994 8.76367 2.05926 8.51567 2.30725C8.26767 2.55523 8.12839 2.89158 8.12839 3.24228V8.86395L20.0324 12.3359L31.9364 8.86395V3.24228C31.9364 2.89158 31.797 2.55523 31.549 2.30725C31.3011 2.05926 30.9647 1.91994 30.6141 1.91994Z"
                                            fill="#41A5EE"></path>
                                        <path
                                            d="M31.9364 8.86395H8.12839V15.8079L20.0324 19.2799L31.9364 15.8079V8.86395Z"
                                            fill="#2B7CD3"></path>
                                        <path
                                            d="M31.9364 15.8079H8.12839V22.7519L20.0324 26.2239L31.9364 22.7519V15.8079Z"
                                            fill="#185ABD"></path>
                                        <path
                                            d="M31.9364 22.752H8.12839V28.3736C8.12839 28.7244 8.26767 29.0607 8.51567 29.3087C8.76367 29.5567 9.09999 29.696 9.45071 29.696H30.6141C30.9647 29.696 31.3011 29.5567 31.549 29.3087C31.797 29.0607 31.9364 28.7244 31.9364 28.3736V22.752Z"
                                            fill="#103F91"></path>
                                        <path opacity="0.1"
                                            d="M16.7261 6.87994H8.12839V25.7279H16.7261C17.0764 25.7269 17.4121 25.5872 17.6599 25.3395C17.9077 25.0917 18.0473 24.756 18.0484 24.4056V8.20226C18.0473 7.8519 17.9077 7.51616 17.6599 7.2684C17.4121 7.02064 17.0764 6.88099 16.7261 6.87994Z"
                                            fill="black"></path>
                                        <path opacity="0.2"
                                            d="M15.7341 7.87194H8.12839V26.7199H15.7341C16.0844 26.7189 16.4201 26.5792 16.6679 26.3315C16.9157 26.0837 17.0553 25.748 17.0564 25.3976V9.19426C17.0553 8.84386 16.9157 8.50818 16.6679 8.26042C16.4201 8.01266 16.0844 7.87299 15.7341 7.87194Z"
                                            fill="black"></path>
                                        <path opacity="0.2"
                                            d="M15.7341 7.87194H8.12839V24.7359H15.7341C16.0844 24.7349 16.4201 24.5952 16.6679 24.3475C16.9157 24.0997 17.0553 23.764 17.0564 23.4136V9.19426C17.0553 8.84386 16.9157 8.50818 16.6679 8.26042C16.4201 8.01266 16.0844 7.87299 15.7341 7.87194Z"
                                            fill="black"></path>
                                        <path opacity="0.2"
                                            d="M14.7421 7.87194H8.12839V24.7359H14.7421C15.0924 24.7349 15.4281 24.5952 15.6759 24.3475C15.9237 24.0997 16.0633 23.764 16.0644 23.4136V9.19426C16.0633 8.84386 15.9237 8.50818 15.6759 8.26042C15.4281 8.01266 15.0924 7.87299 14.7421 7.87194Z"
                                            fill="black"></path>
                                        <path
                                            d="M1.51472 7.87194H14.7421C15.0927 7.87194 15.4291 8.01122 15.6771 8.25922C15.925 8.50722 16.0644 8.84354 16.0644 9.19426V22.4216C16.0644 22.7723 15.925 23.1087 15.6771 23.3567C15.4291 23.6047 15.0927 23.7439 14.7421 23.7439H1.51472C1.16401 23.7439 0.827669 23.6047 0.579687 23.3567C0.3317 23.1087 0.192383 22.7723 0.192383 22.4216V9.19426C0.192383 8.84354 0.3317 8.50722 0.579687 8.25922C0.827669 8.01122 1.16401 7.87194 1.51472 7.87194Z"
                                            fill="#185ABD"></path>
                                        <path
                                            d="M12.0468 20.7679H10.2612L8.17801 13.9231L5.99558 20.7679H4.20998L2.22598 10.8479H4.01158L5.40038 17.7919L7.48358 11.0463H8.97161L10.9556 17.7919L12.3444 10.8479H14.0308L12.0468 20.7679Z"
                                            fill="white"></path>
                                    </svg>
                                    <span class="text-sm dark:text-neutral-200"> my-file.xlsx </span>
                                    <span class="text-xs text-gray-400 dark:text-neutral-500"> 12 Aug, 2021 </span>
                                </a>
                            </div>
                        </main>
                        <footer class="tw-modal-footer py-2">
                            <div class="flex w-full items-center justify-between">
                                <p
                                    class="rounded-sm border border-gray-300 p-0.5 text-xs dark:border-gray-400 dark:text-gray-200">
                                    ESC
                                </p>
                                <a href="https://www.algolia.com/docsearch" class="flex items-center" target="_blank"
                                    rel="noopener noreferrer">
                                    <span class="mr-4 text-xs">Search by</span>
                                    <img src="/images/algolia.png" alt="Algolia" class="h-6" />
                                </a>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
            <!-- End search box -->

            <!-- Theme customizer -->
            <x-theme-customizer />
        </div>
    </body>

</html>
