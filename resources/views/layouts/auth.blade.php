<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Entrar' }} · WiseStore</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 text-gray-800 antialiased">

        <a href="{{ route('public.products.index') }}" wire:navigate class="mb-6 text-2xl font-bold tracking-tight text-gray-900">
            Wise<span class="text-blue-600">Store</span>
        </a>

        <main class="w-full max-w-sm">
            {{ $slot }}
        </main>

        <p class="mt-6 text-xs text-gray-500">© {{ date('Y') }} Wondercell</p>

        @livewireScripts
    </body>
</html>
