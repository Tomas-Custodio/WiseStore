<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Admin' }} · wiseStore</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body x-data="{ sidebarOpen: false }" class="flex min-h-screen bg-gray-50 text-gray-800 antialiased">

        {{-- Fundo escuro do menu (mobile) --}}
        <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

        {{-- Aside --}}
        @php
            $linkBase = 'flex items-center gap-3 rounded-sm px-3 py-1.5 transition hover:bg-gray-800 hover:text-white';
            $linkActive = 'bg-gray-800 text-white';
            $sectionTitle = 'px-3 pb-0.5 pt-2 text-xs uppercase tracking-wide text-gray-500';
            $isOwnProfile = request()->routeIs('users.edit') && request()->route('user')?->is(auth()->user());
        @endphp

        <aside :class="{ 'translate-x-0!': sidebarOpen }"
            class="fixed inset-y-0 left-0 z-50 flex h-screen w-64 shrink-0 -translate-x-full flex-col self-start bg-gray-900 text-gray-300 transition-transform duration-200 lg:sticky lg:top-0 lg:translate-x-0">

            {{-- Logótipo --}}
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 border-b border-gray-800 px-4 py-3">
                {{-- heroicons: shopping-bag --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7 shrink-0 text-blue-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <span>
                    <span class="block text-xl font-bold text-white">Wise<span class="text-blue-500">Store</span></span>
                    <span class="block text-xs font-normal text-gray-500">Painel admin</span>
                </span>
            </a>

            {{-- Navegação (do mais importante para o menos importante) --}}
            <nav class="flex-1 space-y-0.5 overflow-y-auto p-3 text-sm font-medium">

                {{-- Geral --}}
                <a href="{{ route('dashboard') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('dashboard')])>
                    {{-- heroicons: home --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Dashboard
                </a>

                {{-- Loja --}}
                <p class="{{ $sectionTitle }}">Loja</p>

                <a href="{{ route('orders.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('orders.*')])>
                    {{-- heroicons: shopping-bag --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    Encomendas

                    {{-- Nº de comprovativos à espera de decisão --}}
                    @php $ordersInReview = App\Models\Order::where('status', 'review')->count(); @endphp
                    @if ($ordersInReview > 0)
                        <span class="ml-auto rounded-sm bg-blue-600 px-1.5 py-0.5 text-xs font-semibold text-white">{{ $ordersInReview }}</span>
                    @endif
                </a>

                <a href="{{ route('products.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('products.*')])>
                    {{-- heroicons: cube --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                    Produtos
                </a>

                <a href="{{ route('categories.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('categories.*')])>
                    {{-- heroicons: tag --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    Categorias
                </a>

                <a href="{{ route('comments.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('comments.*')])>
                    {{-- heroicons: chat-bubble-left-right --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    Comentários
                </a>

                {{-- Pessoas --}}
                <p class="{{ $sectionTitle }}">Pessoas</p>

                <a href="{{ route('customers.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('customers.*')])>
                    {{-- heroicons: users --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Clientes
                </a>

                <a href="{{ route('users.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('users.*') && ! $isOwnProfile])>
                    {{-- heroicons: shield-check --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    Equipa
                </a>

                {{-- Financeiro --}}
                <p class="{{ $sectionTitle }}">Financeiro</p>

                <a href="{{ route('bank.index') }}" wire:navigate @class([$linkBase, $linkActive => request()->routeIs('bank.*')])>
                    {{-- heroicons: building-library --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                    Contas bancárias
                </a>

                {{-- Conta --}}
                <p class="{{ $sectionTitle }}">Conta</p>

                @auth
                    <a href="{{ route('users.edit', auth()->user()) }}" wire:navigate @class([$linkBase, $linkActive => $isOwnProfile])>
                        {{-- heroicons: user-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        O meu perfil
                    </a>
                @endauth
            </nav>

            {{-- Rodapé do aside --}}
            <div class="space-y-0.5 border-t border-gray-800 p-3 text-sm">

                <a href="{{ route('public.products.index') }}" class="{{ $linkBase }} text-gray-400">
                    {{-- heroicons: building-storefront --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.004 3.004 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                    </svg>
                    Ver loja
                </a>

                @auth
                    <form method="POST" action="{{ route('public.logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full cursor-pointer items-center gap-3 rounded-sm px-3 py-1.5 text-gray-400 transition hover:bg-gray-800 hover:text-red-400">
                            {{-- heroicons: arrow-right-start-on-rectangle --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            Sair
                        </button>
                    </form>
                @endauth

                @guest
                    <a href="{{ route('public.login') }}" class="{{ $linkBase }} text-gray-400">
                        {{-- heroicons: arrow-right-end-on-rectangle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                        </svg>
                        Entrar
                    </a>
                @endguest
            </div>
        </aside>

        {{-- Conteúdo --}}
        <div class="flex min-w-0 flex-1 flex-col">

            {{-- Barra superior (mobile) --}}
            <header class="sticky top-0 z-30 flex items-center gap-3 border-b border-gray-800 bg-gray-900 px-4 py-3 text-white lg:hidden">
                <button type="button" @click="sidebarOpen = true" aria-label="Abrir menu" class="cursor-pointer rounded-sm p-1 hover:bg-gray-800">
                    {{-- heroicons: bars-3 --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <span class="text-lg font-bold">Wise<span class="text-blue-500">Store</span></span>
            </header>

            <main class="flex-1 min-w-0 overflow-hidden p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>