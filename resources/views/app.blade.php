<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @php($meta = $page['props']['meta'] ?? [])
        @php($canonical = $meta['canonical'] ?? url()->current())

        <meta name="description" content="{{ $meta['description'] ?? '' }}">
        <meta name="robots" content="{{ $meta['robots'] ?? 'index, follow' }}">
        <link rel="canonical" href="{{ $canonical }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        <meta property="og:title" content="{{ $meta['title'] ?? config('app.name') }}">
        <meta property="og:description" content="{{ $meta['description'] ?? '' }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $meta['image'] ?? '' }}">
        <meta name="twitter:card" content="summary_large_image">

        @if (! empty($meta['json_ld']))
            <script type="application/ld+json">{!! json_encode($meta['json_ld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        @endif

        {{-- Storefront pages (everything outside the admin, auth and settings shells) use the brand fonts. --}}
        @php($isStorefront = ! Illuminate\Support\Str::startsWith($page['component'], ['auth/', 'admin/', 'settings/']) && $page['component'] !== 'Dashboard')

        @if ($isStorefront)
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
        @endif

        @if ($page['component'] === 'Welcome')
            <link rel="preload" as="image" href="/images/lette/stitch-hero.jpg" fetchpriority="high">
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $meta['title'] ?? config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
