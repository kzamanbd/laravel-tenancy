<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        @include('partials.head')
    </head>

    <body>
        <div class="tw--radial-gradient">
            <div class="flex min-h-svh items-center justify-center overflow-hidden p-6">
                <div class="auth-card">
                    <div class="auth-card-left"></div>
                    {{ $slot }}
                    <div class="auth-card-right"></div>
                </div>
            </div>
        </div>
    </body>

</html>
