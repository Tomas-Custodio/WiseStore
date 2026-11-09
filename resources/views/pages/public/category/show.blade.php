<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')] class extends Component
{
    use WithPagination;

    public Category $category;

    public function mount(Category $category){

        $this->category = $category;
    }

    #[Computed]

    public function products()
    {
        return $this->category->products()
            ->where('status', 'active')
            ->withCount('comments')
            ->latest()
            ->paginate(12);
    }
}; ?>

<article>

    <a href="{{ route('public.categories.index') }}" wire:navigate class="mb-3 inline-block text-sm text-blue-600 hover:underline">
        ← Todas as categorias
    </a>

    <header class="mb-6 flex h-40 items-center gap-4 rounded-sm bg-gradient-to-r from-blue-600 to-blue-500 p-6 shadow-sm">

        {{-- heroicons: tag --}}
        <div class="flex size-16 shrink-0 items-center justify-center rounded-sm bg-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-9 text-white">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>
        </div>

        <div>
            <h1 class="text-2xl font-semibold text-white">{{ $category->name }}</h1>

            @if ($category->description)
                <p class="mt-1 text-sm text-blue-100">{{ $category->description }}</p>
            @endif
        </div>

    </header>

    <p class="text-2xl my-8 font-semibold text-gray-800">Produtos ({{$category->products()->count() }})</p>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

            @forelse ( $this->products as $product )

                {{-- Cartão de produto: tudo leva à página de detalhes --}}

                <a
                    href="{{ route('public.products.show', $product) }}"
                    wire:navigate
                    wire:key="product-{{ $product->id }}"
                    class="group flex flex-col overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm transition hover:shadow-md">

                    <div class="relative aspect-square overflow-hidden bg-gray-100">

                        @if ($product->img)

                            <img
                                src="{{ asset('storage/'.$product->img) }}"
                                alt="{{ $product->name }}"
                                loading="lazy"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105">

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

    <div class="mt-3">
        {{ $this->products->links() }}
    </div>

</article>
