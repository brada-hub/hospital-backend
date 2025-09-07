<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="h-screen bg-slate-200 font-roboto">

    <div class="flex h-full">
        <div class="flex flex-col flex-1 overflow-hidden">
            <main class="flex-1 h-full w-full overflow-y-auto bg-white">
                @livewire('inicio')
                @livewire('prueba')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
