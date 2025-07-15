<div class="card-body">
    <!-- Logo and Title -->
    <div class="my-4 flex items-center justify-center">
        <x-app-logo />
    </div>

    <!-- Welcome Message -->
    <div class="my-3">
        <p class="mb-2 hidden text-2xl font-semibold md:block">Verify your email</p>
        <p class="text-xs">Please verify your email address by clicking on the link we just emailed to you.</p>
    </div>

    <!-- Session Status -->
    @if (session('status') == 'verification-link-sent')
        <div class="text-center mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
            <span class="text-green-600 text-sm font-medium">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </span>
        </div>
    @endif

    <!-- Resend Verification Form -->
    <div class="my-3">
        <button wire:click="sendVerification" class="btn btn-primary w-full mb-4">
            {{ __('Resend verification email') }}
        </button>

        <div class="text-center">
            <button wire:click="logout" class="text-sm text-gray-600 hover:text-gray-800 cursor-pointer">
                {{ __('Log out') }}
            </button>
        </div>
    </div>
</div>
