<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

    <head>
        @include('partials.head')
    </head>

    <body>
        <div class="tw--radial-gradient">
            <div class="flex min-h-svh items-center justify-center overflow-hidden p-6">
                {{ $slot }}
            </div>
        </div>
        @fluxScripts
    </body>

</html>
