<?php

use App\Models\CartItem;
use App\Models\Like;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public Product $product;
    public int $quantity = 1;

    /**
     * Carrega o produto e a sua categoria.
     */
    public function mount(Product $product): void
    {
        if ($product->status !== 'active') {
            abort(404);
        }

        $this->product = $product->load('category');
    }

    /**
     * Conta os likes do produto.
     */
    #[Computed]
    public function likesCount(): int
    {
        return $this->product->likes()->count();
    }

    /**
     * Verifica se o utilizador já gostou do produto.
     */
    #[Computed]
    public function likedByMe(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return $this->product->likes()
            ->where('user_id', auth()->id())
            ->exists();
    }

    /**
     * Procura este produto dentro do carrinho do utilizador.
     */
    #[Computed]
    public function cart(): ?CartItem
    {
        if (! auth()->check()) {
            return null;
        }

        return auth()->user()->cartItems()
            ->where('product_id', $this->product->id)
            ->first();
    }

    /**
     * Procura produtos relacionados.
     */
    #[Computed]
    public function relatedProducts(): Collection
    {
        return Product::query()
            ->where('status', 'active')
            ->where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->withCount(['likes', 'comments'])
            ->latest()
            ->limit(8)
            ->get();
    }

    /**
     * Aumenta a quantidade do seletor.
     */
    public function incrementQuantity(): void
    {
        if ($this->quantity < 99) {
            $this->quantity++;
        }
    }

    /**
     * Diminui a quantidade do seletor.
     */
    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    /**
     * Diminui a quantidade que já está no carrinho (grava na base de dados).
     */
    public function decreaseCart(): void
    {
        if (! auth()->check()) {
            return;
        }

        $cart = $this->cart;

        if ($cart && $cart->quantity > 1) {
            $cart->quantity -= 1;
            $cart->save();
        }

        unset($this->cart);
    }

    /**
     * Adiciona o produto ao carrinho (acumula a quantidade).
     */
    public function addToCart(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('public.login', navigate: true);

            return;
        }

        $quantity = $this->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        // Garante que o utilizador tem um carrinho e soma a quantidade ao produto.
        auth()->user()->cart()->firstOrCreate()->addProduct($this->product, $quantity['quantity']);

        unset($this->cart);
        $this->reset('quantity');
        session()->flash('cart', 'Produto adicionado ao carrinho!');
    }

    public function toggleLike(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('public.login', navigate: true);

            return;
        }

        $like = Like::where('product_id', $this->product->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($like) {
            $like->delete();
        } else {
            Like::create([
                'product_id' => $this->product->id,
                'user_id' => auth()->id(),
            ]);
        }

        unset($this->likesCount, $this->likedByMe);
    }
}; ?>

<article class="mx-auto max-w-7xl px-4 py-6">

    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-6 text-xs text-gray-500">
        <ol class="flex flex-wrap items-center gap-2">
            <li>
                <a href="{{ route('home') }}" wire:navigate class="hover:text-blue-600">
                    Início
                </a>
            </li>

            <li>›</li>

            <li>
                <a href="{{ route('public.products.index') }}" wire:navigate class="hover:text-blue-600">
                    Produtos
                </a>
            </li>

            @if ($product->category)
                <li>›</li>
                <li>
                    <a href="{{ route('public.categories.show', $product->category) }}"
                        wire:navigate
                        class="hover:text-blue-600">
                        {{ $product->category->name }}
                    </a>
                </li>
            @endif

            <li>›</li>

            <li class="truncate text-gray-800" aria-current="page">
                {{ $product->name }}
            </li>
        </ol>
    </nav>


    {{-- DETALHES DO PRODUTO --}}
    <section class="grid grid-cols-1 gap-8 lg:grid-cols-2">

        {{-- Imagem --}}
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <div class="relative aspect-square bg-gray-100">

                @if ($product->img)
                    <img
                        src="{{ asset('storage/' . $product->img) }}"
                        alt="{{ $product->name }}"
                        class="h-full w-full object-contain"
                    >
                @else
                    <div class="flex h-full items-center justify-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-16">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"
                            />
                        </svg>
                    </div>
                @endif

                @if ($product->promotion)
                    <span class="absolute left-4 top-4 rounded bg-red-600 px-3 py-1 text-xs font-semibold text-white">
                        Promoção
                    </span>
                @endif

            </div>
        </div>


        {{-- Informações e compra --}}
        <div class="flex flex-col">

            {{-- Categoria --}}
            @if ($product->category)
                <a href="{{ route('public.categories.show', $product->category) }}"
                    wire:navigate
                    class="text-xs font-semibold uppercase tracking-widest text-blue-600 hover:underline">
                    {{ $product->category->name }}
                </a>
            @endif

            {{-- Nome --}}
            <h1 class="mt-3 text-2xl font-bold text-gray-900 sm:text-3xl">
                {{ $product->name }}
            </h1>

            {{-- Preço --}}
            <div class="mt-5">
                <p class="text-3xl font-bold text-red-600">
                    {{ $product->formattedPrice() }}
                </p>
            </div>

            {{-- Descrição --}}
            <div class="mt-6 border-t border-gray-200 pt-5">
                <h2 class="text-sm font-semibold text-gray-900">
                    Descrição do produto
                </h2>

                <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-gray-600">
                    {{ $product->description }}
                </p>
            </div>


            {{-- Quantidade e carrinho --}}
            <div class="mt-5">

                <label class="mb-3 block text-sm font-semibold text-gray-800">
                    Quantidade
                </label>

                <div class="flex flex-wrap gap-3">

                    <div class="inline-flex items-center rounded border border-gray-300">

                        <button
                            type="button"
                            wire:click="decrementQuantity"
                            @disabled($quantity <= 1)
                            aria-label="Diminuir quantidade"
                            class="px-4 py-3 text-gray-600 hover:bg-gray-100 disabled:opacity-40">
                            −
                        </button>

                        <span class="w-10 text-center text-sm font-medium">
                            {{ $quantity }}
                        </span>

                        <button
                            type="button"
                            wire:click="incrementQuantity"
                            @disabled($quantity >= 99)
                            aria-label="Aumentar quantidade"
                            class="px-4 py-3 text-gray-600 hover:bg-gray-100 disabled:opacity-40">
                            +
                        </button>

                    </div>

                    <button
                        type="button"
                        wire:click="addToCart"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        class="flex flex-1 items-center justify-center gap-2 rounded bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50">

                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272"
                            />
                        </svg>

                        Adicionar ao carrinho
                    </button>

                </div>

                @error('quantity')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                @if (session('cart'))
                    <div class="mt-4 flex items-center justify-between rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                        {{ session('cart') }}

                        <a href="{{ route('public.cart.index') }}"
                            wire:navigate
                            class="font-semibold underline">
                            Ver carrinho
                        </a>
                    </div>
                @endif

            </div>


            {{-- Likes --}}
            <div class="mt-6 border-t border-gray-200 pt-4">

                <button
                    type="button"
                    wire:click="toggleLike"
                    @class([
                        'inline-flex items-center gap-2 rounded px-3 py-2 text-sm transition',
                        'bg-red-50 text-red-600' => $this->likedByMe,
                        'text-gray-500 hover:bg-red-50 hover:text-red-600' => ! $this->likedByMe,
                    ])
                    aria-pressed="{{ $this->likedByMe ? 'true' : 'false' }}"
                    title="{{ $this->likedByMe ? 'Retirar like' : 'Dar like' }}">

                    @if ($this->likedByMe)
                        <svg xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            class="size-5">
                            <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17l-.383.219-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0Z"/>
                        </svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
                        </svg>
                    @endif

                    <span>{{ $this->likesCount }}</span>
                    <span>Gostos</span>
                </button>

            </div>


            {{-- Garantias --}}
            <ul class="mt-6 grid grid-cols-3 gap-3 border-t border-gray-200 pt-5 text-center text-xs text-gray-600">

                <li class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-6 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622"
                        />
                    </svg>
                    Pagamentos seguros
                </li>

                <li class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-6 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25"
                        />
                    </svg>
                    Logística segura
                </li>

                <li class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="size-6 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"
                        />
                    </svg>
                    Privacidade
                </li>

            </ul>

        </div>
    </section>


    {{-- PRODUTOS RELACIONADOS --}}

    @if ($this->relatedProducts->isNotEmpty())
        <section class="mt-16">

            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">
                    Você pode gostar
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Outros produtos que podem despertar o teu interesse.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">

                @foreach ($this->relatedProducts as $related)

                    {{-- Cartão de produto: tudo leva à página de detalhes --}}
                    <a
                        href="{{ route('public.products.show', $related) }}"
                        wire:navigate
                        wire:key="related-{{ $related->id }}"
                        class="group flex flex-col overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm transition hover:shadow-md"
                    >

                        <div class="relative aspect-square overflow-hidden bg-gray-100">

                            @if ($related->img)
                                <img
                                    src="{{ asset('storage/'.$related->img) }}"
                                    alt="{{ $related->name }}"
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

                            {{-- Contador de likes (só mostra, não clica) --}}
                            <span class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-white/90 px-3 py-1.5 text-sm font-medium text-gray-700 shadow-md backdrop-blur">

                                {{-- heroicons: heart --}}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                                <span>{{ $related->likes_count }}</span>
                            </span>

                            @if ($related->promotion)
                                <span class="absolute left-2 top-2 rounded-sm bg-red-600 px-2 py-1 text-xs font-semibold text-white">
                                    Promoção
                                </span>
                            @endif

                        </div>

                        <div class="flex flex-1 flex-col p-4">

                            <h3 class="line-clamp-2 font-medium text-gray-800 group-hover:text-blue-600">
                                {{ $related->name }}
                            </h3>

                            <p class="mt-1 line-clamp-2 text-sm text-gray-500">
                                {{ $related->description }}
                            </p>

                            <p class="mt-auto pt-3 text-lg font-bold text-red-600">
                                {{ $related->formattedPrice() }}
                            </p>

                            <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-xs">

                                {{-- Comentários: leva à página de comentários, não aos detalhes --}}
                                <span
                                    role="link"
                                    tabindex="0"
                                    x-data
                                    @click.prevent.stop="Livewire.navigate('{{ route('public.comments.index', $related) }}')"
                                    @keydown.enter.prevent.stop="Livewire.navigate('{{ route('public.comments.index', $related) }}')"
                                    class="flex cursor-pointer items-center gap-1 text-gray-500 hover:text-blue-600"
                                >
                                    {{-- heroicons: chat-bubble-oval-left --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                                    </svg>
                                    {{ $related->comments_count }}
                                    {{ Str::plural('comentário', $related->comments_count) }}
                                </span>

                                <span class="font-semibold text-blue-600">
                                    Ver detalhes →
                                </span>

                            </div>

                        </div>

                    </a>

                @endforeach

            </div>

        </section>
    @endif

</article>