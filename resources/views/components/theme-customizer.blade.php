<div id="theme-customizer">
    <button type="button" class="customizer-icon btn-primary" data-hs-overlay="#customizer-drawer">
        <span class="icon-[mdi--cog] animate-spin"></span>
    </button>
    <nav id="customizer-drawer" class="hs-overlay tw-drawer customizer-drawer">
        <div class="tw-drawer-header">
            <div>
                <h4 class="customizer-title">THEME CUSTOMIZER</h4>
                <p class="text-sm">Customize and preview in real time</p>
            </div>
            <button type="button" class="tw-modal-close" data-hs-overlay="#customizer-drawer">
                <i class="icon-[mdi--close]"></i>
            </button>
        </div>
        <div class="tw-drawer-body p-0">
            <div data-simplebar class="customizer-container">
                <div class="customizer-card">
                    <h5 class="customizer-header">Color Scheme</h5>
                    <p class="text-xs">Overall light or dark presentation.</p>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div>
                            <input type="radio" id="light-option" value="light" name="theme-option"
                                class="peer theme-switcher hidden" />
                            <label for="light-option"
                                class="btn btn-outline-primary peer-checked:bg-primary peer-checked:text-white">
                                <span class="icon-[mdi--brightness-6]"></span>
                                Light
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="dark-option" value="dark" name="theme-option"
                                class="peer theme-switcher hidden" />
                            <label for="dark-option"
                                class="btn btn-outline-primary peer-checked:bg-primary peer-checked:text-white">
                                <span class="icon-[mdi--brightness-2]"></span>
                                Dark
                            </label>
                        </div>

                        <div>
                            <input type="radio" id="system-option" value="system" name="theme-option"
                                class="peer theme-switcher hidden" />
                            <label for="system-option"
                                class="btn btn-outline-primary peer-checked:bg-primary peer-checked:text-white">
                                <span class="icon-[mdi--brightness-auto]"></span>
                                System
                            </label>
                        </div>
                    </div>
                    <div class="text-primary">
                        <label class="mb-0 inline-flex items-center">
                            <input type="checkbox" class="form-check-input sidebar-semi-dark" id="semi11" />
                            <span class="mx-2">Semi Dark (Sidebar)</span>
                        </label>
                    </div>
                </div>
                <div class="customizer-card">
                    <h5 class="customizer-header">Themes Colors</h5>
                    <p class="text-xs">
                        Select the primary color for your app. This will be used across various components.
                    </p>
                    <div class="my-3 grid grid-cols-3 gap-2">
                        <div>
                            <input type="radio" id="default-option" value="default" name="colors-option"
                                class="peer color-schema hidden" />
                            <label for="default-option"
                                class="btn border-indigo-400 peer-checked:bg-indigo-500 peer-checked:text-white">
                                Default
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="amber-option" value="amber" name="colors-option"
                                class="peer color-schema hidden" />
                            <label for="amber-option"
                                class="btn border-amber-400 peer-checked:bg-amber-500 peer-checked:text-white">
                                Amber
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="rose-option" value="rose" name="colors-option"
                                class="peer color-schema hidden" />
                            <label for="rose-option"
                                class="btn border-rose-400 peer-checked:bg-rose-500 peer-checked:text-white">
                                Rose
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="purple-option" value="purple" name="colors-option"
                                class="peer color-schema hidden" />
                            <label for="purple-option"
                                class="btn border-purple-400 peer-checked:bg-purple-500 peer-checked:text-white">
                                Purple
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="sky-option" value="sky" name="colors-option"
                                class="peer color-schema hidden" />
                            <label for="sky-option"
                                class="btn border-sky-400 peer-checked:bg-sky-500 peer-checked:text-white">
                                Sky
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="teal-option" value="teal" name="colors-option"
                                class="peer color-schema hidden" />
                            <label for="teal-option"
                                class="btn border-teal-400 peer-checked:bg-teal-500 peer-checked:text-white">
                                Teal
                            </label>
                        </div>
                    </div>
                </div>

                <div class="customizer-card">
                    <h5 class="customizer-header">Menu Layout</h5>
                    <p class="text-xs">Select the primary navigation paradigm for your app.</p>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        <div>
                            <input type="radio" id="horizontal-option" value="horizontal"
                                name="navigation-position" class="peer navigation-position hidden" />
                            <label for="horizontal-option"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                Horizontal
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="vertical-option" value="vertical" name="navigation-position"
                                class="peer navigation-position hidden" />
                            <label for="vertical-option"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                Vertical
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="collapsible-option" value="collapsible"
                                name="navigation-position" class="peer navigation-position hidden" />
                            <label for="collapsible-option"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                Collapsible
                            </label>
                        </div>
                    </div>
                </div>

                <div class="customizer-card">
                    <h5 class="customizer-header">Layout Style</h5>
                    <p class="text-xs">Select the primary layout style for your app.</p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div>
                            <input type="radio" id="boxed-layout" value="boxed-layout" name="layout-style"
                                class="peer layout-style hidden" />
                            <label for="boxed-layout"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                Box
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="full-layout" value="full-layout" name="layout-style"
                                class="peer layout-style hidden" />
                            <label for="full-layout"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                Full
                            </label>
                        </div>
                    </div>
                </div>

                <div class="customizer-card">
                    <h5 class="customizer-header">Direction</h5>
                    <p class="text-xs">Select the direction for your app.</p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <div>
                            <input type="radio" id="ltr" value="ltr" name="direction"
                                class="peer layout-direction hidden" />
                            <label for="ltr"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                LTR
                            </label>
                        </div>
                        <div>
                            <input type="radio" id="rtl" value="rtl" name="direction"
                                class="peer layout-direction hidden" />
                            <label for="rtl"
                                class="btn border-primary peer-checked:bg-primary peer-checked:text-white">
                                RTL
                            </label>
                        </div>
                    </div>
                </div>

                <div class="customizer-card">
                    <h5 class="customizer-header">Navbar Type</h5>
                    <p class="text-xs">FIxed or Static.</p>
                    <div class="text-primary mt-3 flex items-center gap-3">
                        <label class="mb-0 inline-flex items-center">
                            <input type="radio" value="navbar-static" class="form-check-input"
                                name="navbarType" />
                            <span class="mx-2">Static</span>
                        </label>
                        <label class="mb-0 inline-flex items-center">
                            <input type="radio" value="navbar-fixed" class="form-check-input" name="navbarType" />
                            <span class="mx-2">Fixed</span>
                        </label>
                        <label class="mb-0 inline-flex items-center">
                            <input type="radio" value="navbar-hidden" class="form-check-input"
                                name="navbarType" />
                            <span class="mx-2">Hidden</span>
                        </label>
                    </div>
                </div>

                <div class="customizer-card">
                    <h5 class="customizer-header">Footer Type</h5>
                    <p class="text-xs">FIxed or Static.</p>
                    <div class="text-primary mt-3 flex items-center gap-3">
                        <label class="mb-0 inline-flex items-center">
                            <input type="radio" value="footer-static" class="form-check-input"
                                name="footerType" />
                            <span class="mx-2">Static</span>
                        </label>
                        <label class="mb-0 inline-flex items-center">
                            <input type="radio" value="footer-fixed" class="form-check-input" name="footerType" />
                            <span class="mx-2">Fixed</span>
                        </label>
                        <label class="mb-0 inline-flex items-center">
                            <input type="radio" value="footer-hidden" class="form-check-input"
                                name="footerType" />
                            <span class="mx-2">Hidden</span>
                        </label>
                    </div>
                </div>

                <div class="customizer-card flex items-center justify-between">
                    <h5 class="customizer-header">Menu Hidden</h5>
                    <div class="text-primary mt-3 flex items-center gap-3">
                        <label class="relative h-6 w-12">
                            <input type="checkbox" class="peer hidden-sidebar hidden" id="hidden-switch" />
                            <span for="hidden-switch" class="hidden-switch"></span>
                        </label>
                    </div>
                </div>

                <div class="customizer-card">
                    <h5 class="customizer-header">Router Transition</h5>
                    <p class="text-xs">Animation of main content.</p>
                    <div class="mt-3">
                        <select id="animation-switcher" class="form-control">
                            <option value>Select Animation</option>
                            <option value="animate__fadeIn">Fade</option>
                            <option value="animate__fadeInDown">Fade Down</option>
                            <option value="animate__fadeInUp">Fade Up</option>
                            <option value="animate__fadeInLeft">Fade Left</option>
                            <option value="animate__fadeInRight">Fade Right</option>
                            <option value="animate__slideInDown">Slide Down</option>
                            <option value="animate__slideInLeft">Slide Left</option>
                            <option value="animate__slideInRight">Slide Right</option>
                            <option value="animate__zoomIn">Zoom In</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>
