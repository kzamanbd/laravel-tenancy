<div class="auth-card">
    <div class="auth-card-left"></div>
    <div class="card-body">
        <!-- Logo and Title -->
        <div class="my-4 flex items-center justify-center">
            <x-app-logo />
        </div>

        <!-- Welcome Message -->
        <div class="my-3">
            <p class="mb-2 hidden text-2xl font-semibold md:block">Forgot password?</p>
            <p class="text-xs">Enter your email to receive a password reset link</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center mb-4" :status="session('status')" />

        <!-- Forgot Password Form -->
        <form wire:submit="sendPasswordResetLink" class="my-3">
            <!-- Email Address -->
            <div class="block">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" wire:model="email" class="form-control"
                    placeholder="email@example.com" autocomplete="email" required autofocus />
                @error('email')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary w-full mt-4">{{ __('Email password reset link') }}</button>
        </form>

        <!-- Login Link -->
        <p class="text-sm">
            {{ __('Or, return to') }}
            <a href="{{ route('login') }}" wire:navigate class="text-primary">{{ __('log in') }}</a>
        </p>
    </div>
    <div class="auth-card-right"></div>
</div>
