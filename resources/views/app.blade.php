<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Apply the persisted theme before first paint. Reads the same
             `themeConfig` cookie that the ThemeProvider owns, so the server
             render, this script, and React all agree and nothing flashes. --}}
        <script>
            (function () {
                var settings = { theme: 'system', themeVariant: 'default', rtlClass: 'ltr' };

                try {
                    var raw = document.cookie
                        .split('; ')
                        .find(function (row) { return row.indexOf('themeConfig=') === 0; });

                    if (raw) {
                        Object.assign(settings, JSON.parse(decodeURIComponent(raw.slice('themeConfig='.length))));
                    }
                } catch (e) {
                    // Malformed cookie: fall through to the defaults above.
                }

                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var isDark = settings.theme === 'dark' || (settings.theme === 'system' && prefersDark);

                var el = document.documentElement;
                el.classList.add(isDark ? 'dark' : 'light');
                el.classList.add('theme-' + (settings.themeVariant || 'default'));
                el.dir = settings.rtlClass || 'ltr';
            })();
        </script>

        {{-- Matches --background in resources/css/base/tailwind.css. --}}
        <style>
            html { background-color: oklch(0.99 0 0); }
            html.dark { background-color: oklch(0 0 0); }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @viteReactRefresh
        @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
