<?php

use App\Models\Like;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /**
     * Último produto adicionado ao carrinho (mostra o ícone de confirmação).
     */
    public ?int $addedProductId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('status', 'active')
            ->withCount(['comments', 'likes'])
            ->withExists(['likes as liked_by_me' => fn ($query) => $query->where('user_id', auth()->id())])
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate(12);
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

        $cart = auth()->user()->carts()->firstOrNew(['product_id' => $product->id]);
        $cart->quantity = min(($cart->quantity ?? 0) + 1, 99);
        $cart->save();

        $this->addedProductId = $product->id;
    }
};
?>

<article>

    <header class="mb-3 flex items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-800">
            Produtos
        </h1>
    </header>

    <input
        wire:model.live.debounce.300ms="search"
        type="search"
        placeholder="Pesquisar produtos..."
        class="mb-3 w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
    />

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

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

                    <div class="mt-2 flex items-center justify-between border-t border-gray-100 pt-2 text-xs">

                        {{-- Comentários: leva à página de comentários, não aos detalhes --}}
                        <span
                            role="link"
                            tabindex="0"
                            x-data
                            @click.prevent.stop="Livewire.navigate('{{ route('public.comments.index', $product) }}')"
                            @keydown.enter.prevent.stop="Livewire.navigate('{{ route('public.comments.index', $product) }}')"
                            class="flex cursor-pointer items-center gap-1 text-gray-500 hover:text-blue-600"
                        >
                            {{-- heroicons: chat-bubble-oval-left --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                            </svg>
                            {{ $product->comments_count }}
                            {{ Str::plural('comentário', $product->comments_count) }}
                        </span>

                        <span class="font-semibold text-blue-600">
                            Ver detalhes →
                        </span>

                    </div>

                </div>

            </a>

        @empty

            <p class="col-span-full rounded-sm border border-gray-200 bg-white p-3 text-center text-sm text-gray-500">
                Nenhum produto encontrado.
            </p>

        @endforelse

    </section>


</article>
