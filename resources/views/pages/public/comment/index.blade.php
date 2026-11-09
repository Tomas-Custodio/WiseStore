<?php

use App\Models\Comment;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')] class extends Component
{
    use WithPagination;

    public Product $product;
    public string $body = '';

    /**
     * Recebe o produto através da rota.
     */
    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    /**
     * Publica um comentário para o produto atual.
     */
    public function save(): void
    {
        $data = $this->validate([
            'body' => 'required|string|max:1000',
        ]);

        Comment::create([
            'user_id' => auth()->id(),
            'product_id' => $this->product->id,
            'body' => $data['body'],
        ]);

        $this->reset('body');
        $this->resetPage();

        session()->flash('msg', 'Comentário publicado!');
    }

    /**
     * Obtém os comentários do produto atual.
     */
    #[Computed]
    public function comments()
    {
        return Comment::query()
            ->where('product_id', $this->product->id)
            ->with('user')
            ->latest()
            ->get();
    }
};
?>

<article class="mx-auto max-w-7xl px-4 py-6">

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">

        {{-- Esquerda: o produto que está a ser comentado --}}

        <section class="lg:col-span-2">

            <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm lg:sticky lg:top-32">

                <div class="relative aspect-[4/3] bg-gray-100">

                    @if ($product->img)
                        <img src="{{ asset('storage/' . $product->img) }}" alt="{{ $product->name }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-gray-300">
                            {{-- heroicons: photo --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                    @endif

                    @if ($product->promotion)
                        <span class="absolute right-2 top-2 flex items-center gap-1 rounded-sm bg-red-600 px-2 py-1 text-xs font-semibold text-white">
                            {{-- heroicons: tag --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                            </svg>
                            Em promoção
                        </span>
                    @endif

                </div>

                <div class="p-4">

                    <h2 class="text-lg font-bold text-gray-900">{{ $product->name }}</h2>

                    <p class="mt-2 text-2xl font-bold text-red-600">
                        {{ number_format($product->preco, 2, ',', '.') }} Kz
                    </p>

                </div>

            </div>

        </section>

        {{-- Direita: comentar em cima, comentários já feitos em baixo --}}

        <section class="lg:col-span-4">

            @if(session('msg'))

                <p class="mb-3 flex items-center gap-2 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{-- heroicons: check-circle --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ session('msg') }}
                </p>

            @endif

            @auth

                <form wire:submit="save" class="mb-4 space-y-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm">

                    <div class="space-y-1">

                        <label for="body" class="flex items-center gap-2 text-sm font-medium text-gray-700">

                            {{-- heroicons: chat-bubble-left --}}

                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>

                            Comentário

                        </label>

                        <textarea wire:model="body" id="body" rows="3" placeholder="Escreve o teu comentário..."
                            class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </textarea>

                        @error('body') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                            class="flex cursor-pointer items-center gap-2 rounded-sm bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            {{-- heroicons: paper-airplane --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            Publicar
                        </button>
                    </div>

                </form>

            @endauth

            @guest

                <p class="mb-4 flex items-center gap-2 rounded-sm border border-gray-200 bg-white p-3 text-sm text-gray-600 shadow-sm">
                    {{-- heroicons: arrow-right-on-rectangle --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    <span>
                        <a href="{{ route('public.login') }}" wire:navigate class="text-blue-600 hover:underline">Entra</a>
                        na tua conta para comentar.
                    </span>
                </p>

            @endguest

            {{-- Comentários já feitos --}}

            <div class="rounded-sm border border-gray-200 bg-white shadow-sm">

                <div class="flex items-center gap-2 border-b border-gray-100 px-4 py-3 text-sm font-semibold text-gray-800">
                    {{-- heroicons: chat-bubble-left-right --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                    </svg>
                    Comentários ({{ $this->comments->count() }})
                </div>

                <ul class="divide-y divide-gray-100">

                    @forelse ($this->comments as $comment)

                        <li wire:key="comment-{{ $comment->id }}" class="flex gap-3 px-4 py-3">

                            {{-- heroicons: user-circle --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-9 shrink-0 text-gray-300">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>

                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $comment->user?->name ?? 'Utilizador removido' }}</p>
                                <p class="mt-1 text-sm text-gray-800">{{ $comment->body }}</p>
                                <p class="mt-1 flex items-center gap-1 text-xs text-gray-500">
                                    {{-- heroicons: clock --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ $comment->created_at?->format('d/m/Y') }}
                                </p>
                            </div>

                        </li>

                    @empty

                        <li class="flex flex-col items-center gap-2 px-4 py-8 text-center text-sm text-gray-500">
                            {{-- heroicons: chat-bubble-left --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            Ainda não há comentários. Sê o primeiro!
                        </li>

                    @endforelse

                </ul>

            </div>

        </section>

    </div>

</article>