<div class="auth-card">
    <div class="auth-card-left"></div>
    <div class="card-body">
        <!-- Logo and Title -->
        <div class="my-4 flex items-center justify-center">
            <x-app-logo />
        </div>

        <!-- Welcome Message -->
        <div class="my-3">
            <p class="mb-2 hidden text-2xl font-semibold md:block">Confirm password</p>
            <p class="text-xs">This is a secure area of the application. Please confirm your password before continuing.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center mb-4" :status="session('status')" />

        <!-- Confirm Password Form -->
        <form wire:submit="confirmPassword" class="my-3">
            <!-- Password -->
            <div class="block">
                <label for="password" class="form-label">{{ __('Password') }}</label>
                <div class="relative">
                    <input id="password" type="password" name="password" wire:model="password" class="form-control"
                        placeholder="{{ __('Password') }}" autocomplete="current-password" required />
                    <button type="button"
                        data-hs-toggle-password='{
                                    "target": ["#password"]
                                  }'
                        class="absolute inset-y-0 end-0 z-20 flex cursor-pointer items-center rounded-e-md px-3 text-gray-400 focus:text-blue-600 focus:outline-hidden dark:text-neutral-600 dark:focus:text-blue-500">
                        <svg class="size-3.5 shrink-0 dark:text-gray-200" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path class="hs-password-active:hidden" d="M9.88 9.88a3 3 0 1 0 4.24 4.24"></path>
                            <path class="hs-password-active:hidden"
                                d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68">
                            </path>
                            <path class="hs-password-active:hidden"
                                d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61">
                            </path>
                            <line class="hs-password-active:hidden" x1="2" x2="22" y1="2"
                                y2="22"></line>
                            <path class="hs-password-active:block hidden"
                                d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"></path>
                            <circle class="hs-password-active:block hidden" cx="12" cy="12" r="3">
                            </circle>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-full mt-4">{{ __('Confirm') }}</button>
        </form>
    </div>
    <div class="auth-card-right"></div>
</div>
