<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead

        <script>
        window.CONFIG = window.CONFIG || {};
        window.CONFIG.APP_NAME = "{{ config('app.name', 'Laravel') }}";
        window.CONFIG.APP_VERSION = {{ config('app.version', 0x010000) }};
        window.CONFIG.APP_VERSION_STR = "{{ config('app.version_str', '1.0.0') }}";
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
