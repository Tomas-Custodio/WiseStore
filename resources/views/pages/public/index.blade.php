<?php

use App\Models\Category;
use App\Models\Like;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Início')] class extends Component
{
    /**
     * Último produto adicionado ao carrinho (mostra o ícone de confirmação).
     */
    public ?int $addedProductId = null;

    /**
     * Categorias com mais produtos, para a grelha de categorias.
     *
     * @return Collection<int, Category>
     */
    #[Computed]
    public function categories(): Collection
    {
        return Category::withCount('products')
            ->orderByDesc('products_count')
            ->take(6)
            ->get();
    }

    /**
     * Produtos mais recentes em destaque.
     *
     * @return Collection<int, Product>
     */
    #[Computed]
    public function products(): Collection
    {
        return $this->activeProductsQuery()->latest()->take(6)->get();
    }

    /**
     * Produtos em promoção.
     *
     * @return Collection<int, Product>
     */
    #[Computed]
    public function promotions(): Collection
    {
        return $this->activeProductsQuery()->where('promotion', true)->latest()->take(4)->get();
    }

    /**
     * Produto mostrado em destaque no hero.
     */
    #[Computed]
    public function heroProduct(): ?Product
    {
        return Product::where('status', 'active')->whereNotNull('img')->latest()->first();
    }

    /**
     * Números da loja mostrados no hero.
     *
     * @return array{products: int, categories: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'products' => Product::where('status', 'active')->count(),
            'categories' => Category::count(),
        ];
    }

    public function toggleLike(int $productId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('public.login', navigate: true);

            return;
        }

        $product = Product::where('status', 'active')->findOrFail($productId);

        $like = Like::where('product_id', $product->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();

            return;
        }

        Like::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Adiciona 1 unidade do produto ao carrinho (máximo 99 por produto).
     */
    public function addToCart(int $productId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('public.login', navigate: true);

            return;
        }

        $product = Product::where('status', 'active')->findOrFail($productId);

        // Garante que o utilizador tem um carrinho e soma 1 unidade ao produto.
        auth()->user()->cart()->firstOrCreate()->addProduct($product);

        $this->addedProductId = $product->id;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Product>
     */
    private function activeProductsQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Product::query()
            ->where('status', 'active')
            ->with('category')
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_me' => fn ($query) => $query->where('user_id', auth()->id())]);
    }
};
?>

<div class="space-y-20 px-4 ">

    {{-- ============ HERO ============ --}}

    <section class="relative overflow-hidden rounded-3xl 
        bg-gradient-to-br from-gray-900 via-gray-900 to-blue-900 text-white">

        {{-- Brilhos decorativos --}}

        <div class="pointer-events-none 
            absolute -right-24 -top-24 size-96 rounded-full bg-blue-600/30 blur-3xl">
        </div>

        <div class="pointer-events-none absolute -bottom-32 
                -left-20 size-80 rounded-full bg-indigo-500/20 blur-3xl">
        </div>


        <div class="relative grid items-center gap-10 px-6 py-14 
            sm:px-12 lg:grid-cols-2 lg:py-20">

            <div>
                {{-- chama atencao ( micro ) --}}

             

                {{-- mensagem chamativa --}}

                <h1 class="mt-6 text-4xl  font-medium leading-tight 
                    tracking-tight sm:text-5xl lg:text-6xl"> 
                    Tecnologia que
                    <span class="bg-gradient-to-r from-blue-400 to-cyan-300
                        bg-clip-text text-transparent"> te acompanha</span>, entregue à tua porta.
                </h1>

                {{-- mensagem chamativa com destaque de importante --}}

                <p class="mt-5 max-w-lg text-base text-gray-300 sm:text-lg">
                    Smartphones, acessórios e muito mais aos melhores preços de Angola.
                    Compra online e paga na entrega, sem complicações.
                </p>

                {{-- cartoes de acesso a categorias --}}

                <div class="mt-8 flex items-center flex-wrap gap-3">

                    {{-- rota de acesso para todos os produtos --}}

                    <a href="{{ route('public.products.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold shadow-lg shadow-blue-600/30 transition hover:bg-blue-500">

                        Comprar agora

                        {{-- heroicons: arrow-right --}}

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 
                                12m0 0-7.5 7.5M21 12H3" />
                        </svg>

                    </a>

                    {{-- rota de acesso para todas as categorias --}}

                    <a href="{{ route('public.categories.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Ver categorias
                    </a>
                </div>

                {{-- Estatísticas --}}

                <dl class="mt-10 grid max-w-md grid-cols-3 gap-6 border-t border-white/10 pt-6">

                    {{-- card que que conta o numero de produtos activos --}}

                    <div>
                        <dt class="text-xs text-gray-400">Produtos</dt>
                        <dd class="mt-1 text-2xl font-bold">{{ $this->stats['products'] }}+</dd>
                    </div>

                    {{-- card que que conta o total de categorias --}}

                    <div>
                        <dt class="text-xs text-gray-400">Categorias</dt>
                        <dd class="mt-1 text-2xl font-bold">{{ $this->stats['categories'] }}</dd>
                    </div>


                    <div>
                        <dt class="text-xs text-gray-400">Entrega</dt>
                        <dd class="mt-1 text-2xl font-bold">24h</dd>
                    </div>

                </dl>
            </div>

            {{-- Produto em destaque --}}

            <div class="relative mx-auto w-full max-w-md">

                <div class="absolute inset-0 -rotate-6 rounded-3xl bg-gradient-to-tr from-blue-600 to-cyan-400 opacity-60"></div>

                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">

                    @if ($this->heroProduct)

                        <a href="{{ route('public.products.show', $this->heroProduct) }}" wire:navigate class="block">
                            <div class="aspect-square overflow-hidden rounded-2xl bg-white/10">
                                <img src="{{ asset('storage/' . $this->heroProduct->img) }}"
                                    alt="{{ $this->heroProduct->name }}"
                                    class="h-full w-full object-cover transition duration-500 hover:scale-105">
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-4 px-1">
                                <div class="min-w-0">
                                    <p class="text-xs uppercase tracking-wide text-blue-300">Acabou de chegar</p>
                                    <p class="truncate text-lg font-semibold">{{ $this->heroProduct->name }}</p>
                                </div>
                                <p class="shrink-0 rounded-lg bg-white px-3 py-1.5 text-sm font-bold text-gray-900">
                                    {{ $this->heroProduct->formattedPrice() }}
                                </p>
                            </div>
                        </a>
                    @else
                        <div class="flex aspect-square items-center justify-center rounded-2xl bg-white/10">
                            {{-- heroicons: device-phone-mobile --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="size-40 text-white/40">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </section>

    {{-- ============ VANTAGENS ============ --}}

    <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        @foreach ([
            ['title' => 'Pagamento na entrega', 'text' => 'Só pagas quando recebes.', 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
            ['title' => 'Produtos originais', 'text' => 'Garantia em todas as compras.', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z'],
            ['title' => 'Trocas fáceis', 'text' => 'Até 7 dias para trocar.', 'icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
            ['title' => 'Apoio dedicado', 'text' => 'Fala connosco a qualquer hora.', 'icon' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155'],
        ] as $perk)
            <div class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $perk['icon'] }}" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $perk['title'] }}</p>
                    <p class="text-xs text-gray-500">{{ $perk['text'] }}</p>
                </div>
            </div>
        @endforeach
    </section>

    {{-- ============ CATEGORIAS ============ --}}


    @if ($this->categories->isNotEmpty())

        <section>

            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Explora</p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Compra por categoria</h2>
                </div>
                <a href="{{ route('public.categories.index') }}" wire:navigate class="text-sm font-semibold text-blue-600 hover:underline">
                    Todas as categorias →
                </a>
            </div>

          {{--   seccao de categorias com mais produtos --}}

           <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">

    @foreach ($this->categories as $category)
        @php
            $palette = ['from-blue-500 to-cyan-400', 'from-violet-500 to-fuchsia-400', 'from-amber-500 to-orange-400', 'from-emerald-500 to-teal-400', 'from-rose-500 to-pink-400', 'from-indigo-500 to-sky-400'][$loop->index % 6];
        @endphp

        <a href="{{ route('public.categories.show', $category) }}" wire:navigate wire:key="category-{{ $category->id }}"
            class="group relative flex flex-col items-center overflow-hidden rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm transition duration-300 hover:-translate-y-1.5 hover:border-transparent hover:shadow-xl">

            {{-- Círculo decorativo no canto --}}
            <span class="absolute -right-8 -top-8 size-28 rounded-full bg-gradient-to-br {{ $palette }} opacity-10 transition duration-300 group-hover:scale-150 group-hover:opacity-20"></span>

            {{-- Letra da categoria --}}
            <span class="relative flex size-16 items-center justify-center rounded-2xl bg-gradient-to-br {{ $palette }} text-2xl font-bold text-white shadow-lg ring-4 ring-white transition duration-300 group-hover:rotate-6 group-hover:scale-110">
                {{ Str::upper(Str::substr($category->name, 0, 1)) }}
            </span>

            {{-- Nome --}}
            <p class="relative mt-4 line-clamp-1 text-sm font-semibold text-gray-900 transition group-hover:text-blue-600">
                {{ $category->name }}
            </p>

            {{-- Contador --}}
            <span class="relative mt-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 transition group-hover:bg-gray-900 group-hover:text-white">
                {{ $category->products_count }} {{ Str::plural('produto', $category->products_count) }}
            </span>

            {{-- Seta que aparece no hover --}}
            {{-- heroicons: arrow-right --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                class="relative mt-3 size-4 translate-y-1 text-blue-600 opacity-0 transition duration-300 group-hover:translate-y-0 group-hover:opacity-100">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>

        </a>

    @endforeach

</div>

        </section>

    @endif

    {{-- ============ PRODUTOS EM DESTAQUE ============ --}}

    <section>

        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Novidades</p>
                <h2 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Produtos em destaque</h2>
                <p class="mt-2 text-sm text-gray-500">Os produtos mais recentes da nossa loja.</p>
            </div>
            <a href="{{ route('public.products.index') }}" wire:navigate class="text-sm font-semibold text-blue-600 hover:underline">
                Ver todos os produtos →
            </a>

        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">

            @forelse ($this->products as $product)

                {{-- Cartão de produto: tudo leva à página de detalhes --}}
                <a
                    href="{{ route('public.products.show', $product) }}"
                    wire:navigate
                    wire:key="product-{{ $product->id }}"
                    class="group flex flex-col overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                >

                    <div class="relative aspect-square overflow-hidden bg-gray-100">

                        @if ($product->img)
                            <img
                                src="{{ asset('storage/'.$product->img) }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            >
                        @else
                            <div class="flex h-full items-center justify-center">
                                {{-- heroicons: photo --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                        @endif

                        {{-- Like --}}
                        <button type="button" wire:click.prevent.stop="toggleLike({{ $product->id }})"
                            @class([
                                'absolute right-2 top-2 flex cursor-pointer items-center gap-1 rounded-full px-3 py-1.5 text-sm font-medium shadow-md backdrop-blur transition',
                                'bg-red-500 text-white' => $product->liked_by_me,
                                'bg-white/90 text-gray-700 hover:bg-red-50 hover:text-red-500' => ! $product->liked_by_me,
                            ])
                            aria-pressed="{{ $product->liked_by_me ? 'true' : 'false' }}"
                            title="{{ $product->liked_by_me ? 'Remover like' : 'Gostei' }}">
                            {{-- heroicons: heart --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                fill="{{ $product->liked_by_me ? 'currentColor' : 'none' }}" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                            </svg>
                            <span>{{ $product->likes_count }}</span>
                        </button>

                        {{-- Adicionar ao carrinho --}}
                        <button type="button" wire:click.prevent.stop="addToCart({{ $product->id }})"
                            wire:loading.attr="disabled" wire:target="addToCart({{ $product->id }})"
                            @class([
                                'absolute bottom-2 right-2 flex size-10 cursor-pointer items-center justify-center rounded-full shadow-md backdrop-blur transition disabled:opacity-60',
                                'bg-green-500 text-white' => $addedProductId === $product->id,
                                'bg-white/90 text-gray-700 hover:bg-blue-600 hover:text-white' => $addedProductId !== $product->id,
                            ])
                            title="{{ $addedProductId === $product->id ? 'Adicionado ao carrinho' : 'Adicionar ao carrinho' }}"
                            aria-label="Adicionar {{ $product->name }} ao carrinho">

                            @if ($addedProductId === $product->id)
                                {{-- heroicons: check --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @else
                                {{-- heroicons: shopping-cart --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                </svg>
                            @endif
                        </button>

                        @if ($product->promotion)
                            <span class="absolute left-2 top-2 rounded-sm bg-red-600 px-2 py-1 text-xs font-semibold text-white">
                                Promoção
                            </span>
                        @endif

                    </div>

                    <div class="flex flex-1 flex-col p-4">

                        <h2 class="line-clamp-2 font-medium text-gray-800 group-hover:text-blue-600">
                            {{ $product->name }}
                        </h2>

                        <p class="mt-1 line-clamp-2 text-sm text-gray-500">
                            {{ $product->description }}
                        </p>

                        <p class="mt-auto pt-3 text-lg font-bold text-red-600">
                            {{ $product->formattedPrice() }}
                        </p>

                        <div class="mt-2 flex items-center justify-end border-t border-gray-100 pt-2 text-xs">
                            <span class="font-semibold text-blue-600">
                                Ver detalhes →
                            </span>
                        </div>

                    </div>

                </a>

            @empty

                <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-12 text-center">
                    <p class="text-gray-500">Nenhum produto encontrado.</p>
                </div>

            @endforelse

        </div>
    </section>


    {{-- ============  apenas produtos com promocao activa ============ --}}

    @if ($this->promotions->isNotEmpty())

        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-red-600 to-orange-500 p-6 text-white sm:p-10">

            <div class="pointer-events-none absolute -right-10 -top-10 size-64 rounded-full bg-white/10 blur-2xl">
            </div>

            <div class="relative mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="inline-flex items-center gap-1 text-sm font-semibold uppercase tracking-wide text-red-100">
                        {{-- heroicons: bolt --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="size-4">
                            <path d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
                        </svg>
                        Ofertas imperdíveis
                    </p>
                    <h2 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight">Promoções da semana</h2>
                </div>
                <a href="{{ route('public.products.index') }}" wire:navigate
                    class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-red-600 shadow transition hover:bg-red-50">
                    Ver todas as ofertas
                </a>
            </div>

            <div class="relative grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

                @foreach ($this->promotions as $promo)

                    <a href="{{ route('public.products.show', $promo) }}" wire:navigate wire:key="promo-{{ $promo->id }}"

                        class="group flex items-center gap-4 rounded-2xl bg-white p-3 text-gray-900 shadow-lg transition hover:-translate-y-1">

                        <div class="size-20 shrink-0 overflow-hidden rounded-xl bg-gray-100">
                            @if ($promo->img)
                                <img src="{{ asset('storage/' . $promo->img) }}" alt="{{ $promo->name }}"
                                    class="h-full w-full object-cover transition group-hover:scale-110">
                            @endif
                        </div>

                        <div class="min-w-0">
                            <p class="line-clamp-2 text-sm font-semibold">{{ $promo->name }}</p>
                            <p class="mt-1 font-extrabold text-red-600">{{ $promo->formattedPrice() }}</p>
                        </div>

                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ COMO FUNCIONA ============ --}}

    <section class="text-center">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-600">Simples e rápido</p>
        <h2 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Como funciona</h2>

        <ol class="mt-10 grid gap-6 md:grid-cols-3">

            @foreach ([ ['title' => 'Escolhe', 
                        'text' => 'Navega pelas categorias e encontra o produto ideal para ti.'],
                        ['title' => 'Encomenda', 'text' => 'Adiciona ao carrinho e confirma o pedido em poucos cliques.'],
                        ['title' => 'Recebe', 'text' => 'Entregamos em casa e só pagas quando receberes.'],
            ] as $step)
                <li class="relative rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white shadow-lg shadow-blue-600/30">
                        {{ $loop->iteration }}
                    </span>
                    <p class="mt-5 text-lg font-semibold text-gray-900">{{ $step['title'] }}</p>
                    <p class="mt-2 text-sm text-gray-500">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

   
    {{-- ============ CTA FINAL ============ --}}

    <section class="relative overflow-hidden rounded-3xl bg-blue-600 px-2 py-8 text-center
             text-white sm:px-8">

        <div class="pointer-events-none absolute -left-16 -top-16 
            size-64 rounded-full bg-white/10 blur-2xl">
        </div>

        <div class="pointer-events-none absolute 
            -bottom-20 -right-10 size-72 rounded-full
             bg-cyan-300/20 blur-2xl">
        </div>

        <div class="relative mx-auto max-w-2xl">

            @guest
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">
                    Cria a tua conta e começa a comprar</h2>
                <p class="mt-4 text-blue-100">Guarda os teus favoritos, comenta produtos e acompanha as tuas encomendas.</p>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('public.users.create') }}" wire:navigate
                        class="rounded-xl bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-lg transition hover:bg-blue-50">
                        Criar conta grátis
                    </a>
                    <a href="{{ route('public.login') }}" wire:navigate
                        class="rounded-xl border border-white/30 px-6 py-3 text-sm font-semibold transition hover:bg-white/10">
                        Já tenho conta
                    </a>
                </div>

            @else
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Olá,
                    {{ Str::before(auth()->user()->name, ' ') }}! Pronto para mais?
                </h2>

                <p class="mt-4 text-blue-100">Descobre os novos 
                    produtos que chegaram à loja esta semana.
                </p>

                <div class="mt-8">
                    <a href="{{ route('public.products.index') }}" wire:navigate
                        class="inline-flex rounded-xl bg-white px-6 py-3 text-sm font-semibold text-blue-700 shadow-lg transition hover:bg-blue-50">
                        Explorar produtos
                    </a>
                </div>

            @endguest
        </div>
    </section>

</div>
