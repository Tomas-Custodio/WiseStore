<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'WiseStore' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>

    <body class="flex min-h-dvh flex-col overflow-x-clip
         bg-gray-50 text-gray-800 antialiased">

        {{-- Barra de aviso --}}

        <div class="flex items-center justify-center gap-2
             bg-gray-900 px-3 py-2 text-center text-[11px] leading-snug text-gray-100 sm:px-4 sm:text-xs">
            {{-- heroicons: truck --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
            Entregas em toda Luanda · Pagamento na entrega disponível
        </div>

        {{-- Header (só fica fixo se o ecrã tiver altura suficiente, para não ocupar o ecrã todo em telemóvel deitado) --}}

        <header class="top-0 z-40 bg-white border-b border-gray-200 [@media(min-height:640px)]:sticky">
            <div class="max-w-7xl mx-auto px-4 py-2.5 sm:py-3 flex flex-wrap items-center gap-x-2 gap-y-2 sm:gap-x-4">

                 <a href="{{ url('/') }}" class="flex shrink-0 items-center tracking-tight text-gray-900 gap-2">

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                            stroke="currentColor" class="size-6 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 
                                3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 
                                1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                <span>
                    <span class="block text-xl sm:text-3xl font-medium  text-black">Wise<span class="text-blue-500">Store</span></span>
    
                </span>
            </a>


                {{-- Pesquisa --}}

                <form action="{{ url('/') }}" method="GET" class="hidden md:flex min-w-0 flex-1 max-w-xl mx-auto">

                    <input type="text" name="q" placeholder="Pesquisar produtos..."
                        class="w-full min-w-0 px-3 py-2 text-sm border border-gray-300 rounded-sm focus:outline-none focus:border-blue-600">
                    <button type="submit"
                        class="flex shrink-0 items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-sm hover:bg-blue-700 ml-2">
                        {{-- heroicons: magnifying-glass --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Buscar
                    </button>
                </form>

                {{-- Ações --}}
                <nav class="flex flex-wrap items-center justify-end gap-0.5 sm:gap-2 ml-auto text-sm font-medium">
                    @auth
                        @can('only-admin')
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-1 rounded-sm p-1.5 text-gray-600 hover:text-blue-600">
                                {{-- heroicons: squares-2x2 --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                                <span class="hidden lg:inline">Painel admin</span>
                            </a>
                        @endcan

                        <a href="{{ route('public.encomendas.index') }}" wire:navigate class="flex items-center gap-1 rounded-sm p-1.5 text-gray-600 hover:text-blue-600">
                            {{-- heroicons: clipboard-document-list --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                            </svg>
                            <span class="hidden lg:inline">Minhas encomendas</span>
                        </a>


                        <form method="POST" action="{{ route('public.logout') }}">
                            @csrf
                            <button type="submit" class="flex cursor-pointer items-center gap-1 rounded-sm p-1.5 text-gray-600 hover:text-red-600">
                                {{-- heroicons: arrow-right-on-rectangle (espelhado para sair) --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 -scale-x-100">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                </svg>
                                <span class="hidden lg:inline">Sair</span>
                            </button>
                        </form>
                    @endauth

                    @guest
                        <a href="{{ route('public.login') }}" wire:navigate class="flex items-center gap-1 rounded-sm p-1.5 text-gray-600 hover:text-blue-600">
                            {{-- heroicons: arrow-right-on-rectangle --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                            </svg>
                            <span class="hidden sm:inline">Entrar</span>
                        </a>
                        <a href="{{ route('public.users.create') }}" wire:navigate class="flex items-center gap-1 rounded-sm p-1.5 text-gray-600 hover:text-blue-600">
                            {{-- heroicons: user-plus --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                            </svg>
                            <span class="hidden sm:inline">Criar conta</span>
                        </a>
                    @endguest

        @auth      
            <a href="{{ route('public.cart.index') }}" wire:navigate aria-label="Carrinho"
                class="flex items-center gap-1 rounded-sm p-1.5 text-gray-600 transition hover:text-blue-600">

                {{-- Ícone com a bolha vermelha por cima (fica alinhado com os outros ícones) --}}

                <span class="relative">

                    {{-- heroicons: shopping-cart --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                    </svg>

                    <span class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold leading-none text-white ring-2 ring-white">
                        {{ auth()->user()->cartItems()->count() }}
                    </span>
                </span>

                <span class="ml-1 hidden lg:inline">Carrinho</span>

            </a>

            {{-- Perfil do utilizador (avatar com a inicial do nome) --}}

            <a href="{{ route('public.profile') }}" wire:navigate aria-label="Meu perfil" title="{{ auth()->user()->name }}"
                @class([
                    'flex items-center gap-1.5 rounded-sm p-1.5 transition hover:text-blue-600',
                    'text-blue-600' => request()->routeIs('public.profile'),
                    'text-gray-600' => ! request()->routeIs('public.profile'),
                ])>

                <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-blue-600 text-[11px] font-bold uppercase text-white">
                    {{ Str::substr(auth()->user()->name, 0, 1) }}
                </span>

                <span class="hidden max-w-32 truncate lg:inline">{{ Str::before(auth()->user()->name, ' ') }}</span>
            </a>
    @endauth

                </nav>
            </div>

            {{-- Pesquisa (mobile) --}}

            <form action="{{ url('/') }}" method="GET" class="md:hidden flex w-full max-w-7xl mx-auto px-4 pb-3">
                <input type="text" name="q" placeholder="Pesquisar produtos..."
                    class="w-full min-w-0 flex-1 px-3 py-2 text-base border border-gray-300 rounded-sm focus:outline-none focus:border-blue-600">
                <button type="submit" aria-label="Buscar"
                    class="flex shrink-0 items-center px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-sm hover:bg-blue-700 ml-2">
                    {{-- heroicons: magnifying-glass --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </form>

            {{-- Categorias --}}
            
            <div class="border-t border-gray-100 bg-white">
                <ul class="max-w-7xl mx-auto px-4 py-1.5 sm:py-2 flex items-center gap-5 sm:gap-6 overflow-x-auto text-sm text-gray-600 whitespace-nowrap [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

                    <li class="shrink-0">
                        <a href="{{ url('/') }}" class="flex items-center gap-1 py-1 hover:text-blue-600">
                            {{-- heroicons: home --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Início
                        </a>
                    </li>
                    
                    <li class="shrink-0">
                        <a href="{{ route('public.products.index') }}" wire:navigate class="flex items-center gap-1 py-1 hover:text-blue-600">
                               {{-- heroicons: cube --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            productos
                        </a>
                    </li>
                  
                    <li class="shrink-0">
                        <a href="{{ route('public.categories.index') }}" wire:navigate class="flex items-center gap-1 py-1 hover:text-blue-600">
                            {{-- heroicons: squares-2x2 --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            Categorias
                        </a>
                    </li>


                    @foreach (\App\Models\Category::wherehas('products')->orderBy('name')->limit(6)->get(['id', 'name']) as $category)

                        <li class="shrink-0">
                            <a href="{{ route('public.categories.show', $category) }}" wire:navigate 
                            class="block py-1 hover:text-blue-600"> {{ $category->name }}</a>
                        </li>

                    @endforeach

                    <li class="shrink-0">
                        <a href="{{ route('public.promotions.index') }}" wire:navigate class="flex items-center gap-1 py-1 text-red-600 font-medium hover:text-red-700">
                            {{-- heroicons: bolt --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                            </svg>
                            Promoções
                        </a>
                    </li>

                </ul>
            </div>
        </header>

        {{-- Conteúdo --}}
        <main class="min-w-0 flex-1 w-full max-w-7xl mx-auto px-4 py-6 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>

        {{-- Benefícios --}}
        <section class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4 text-sm">
                <div class="flex items-center gap-3 p-3 sm:p-4 border border-gray-200 rounded-sm">
                    {{-- heroicons: truck --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 shrink-0 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                    </svg>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">Entrega rápida</p>
                        <p class="text-gray-500">Recebe o teu pedido em poucos dias.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 sm:p-4 border border-gray-200 rounded-sm">
                    {{-- heroicons: shield-check --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 shrink-0 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">Compra segura</p>
                        <p class="text-gray-500">Pagamento protegido e confiável.</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 sm:p-4 border border-gray-200 rounded-sm">
                    {{-- heroicons: chat-bubble-left --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 shrink-0 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                    </svg>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900">Suporte</p>
                        <p class="text-gray-500">Ajuda sempre que precisares.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-gray-900 text-gray-400 text-sm">
            <div class="max-w-7xl mx-auto px-4 py-8 sm:py-10 grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-8 md:gap-8">

                {{-- Marca --}}

                <div class="col-span-2 md:col-span-1">
                    <a href="{{ url('/') }}" class="mb-2 inline-flex items-center gap-2 text-lg font-bold text-white">
                        {{-- heroicons: shopping-bag --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                        Wise<span class="text-blue-400">Store</span>
                    </a>
                    <p>A tua loja online em Angola.</p>
                    <p class="mt-3 flex items-center gap-2">
                        {{-- heroicons: building-library --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        Pagamento por transferência bancária
                    </p>
                </div>

                {{-- Loja --}}

                <div class="min-w-0">
                    <p class="font-semibold text-white mb-2">Loja</p>
                    <ul class="space-y-2 md:space-y-1">
                        <li>
                            <a href="{{ route('public.products.index') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                {{-- heroicons: cube --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                Produtos
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.categories.index') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                {{-- heroicons: squares-2x2 --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                                </svg>
                                Categorias
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.promotions.index') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                {{-- heroicons: bolt --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                                </svg>
                                Promoções
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Conta --}}

                <div class="min-w-0">
                    <p class="font-semibold text-white mb-2">Conta</p>
                    <ul class="space-y-2 md:space-y-1">
                        @guest
                            <li>
                                <a href="{{ route('public.login') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                    {{-- heroicons: arrow-right-on-rectangle --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                    </svg>
                                    Entrar
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.users.create') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                    {{-- heroicons: user-plus --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                                    </svg>
                                    Criar conta
                                </a>
                            </li>
                        @endguest

                        @auth
                            <li>
                                <a href="{{ route('public.profile') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                    {{-- heroicons: user-circle --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Meu perfil
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.encomendas.index') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                    {{-- heroicons: clipboard-document-list --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>
                                    Minhas encomendas
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.cart.index') }}" wire:navigate class="flex items-center gap-2 py-0.5 hover:text-white">
                                    {{-- heroicons: shopping-cart --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                    </svg>
                                    Carrinho
                                </a>
                            </li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 px-4 text-center text-xs py-4">
                © {{ date('Y') }} WiseStore. Todos os direitos reservados.
            </div>
        </footer>

        @livewireScripts
    </body>
</html>