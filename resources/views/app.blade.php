<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/js/app.ts','resources/css/app.css', "resources/js/pages/{$page['component']}.vue"])
        @routes
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
        {{-- <div id="app"></div> --}}
    </body>
</html>
