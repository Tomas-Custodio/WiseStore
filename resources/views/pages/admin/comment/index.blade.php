<?php

use App\Models\Comment;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Card 1: total de comentários da loja.
     */
    #[Computed]
    public function total(): int
    {
        return Comment::count();
    }

    /**
     * Card 2: comentários feitos desde o dia 1 deste mês.
     */
    #[Computed]
    public function comentariosDoMes(): int
    {
        return Comment::where('created_at', '>=', now()->startOfMonth())->count();
    }

    /**
     * Card 3: os 3 produtos com mais comentários.
     * withCount('comments') cria o atributo comments_count em cada produto.
     *
     * @return Collection<int, Product>
     */
    #[Computed]
    public function produtosMaisComentados(): Collection
    {
        return Product::has('comments')
            ->withCount('comments')
            ->orderByDesc('comments_count')
            ->take(3)
            ->get();
    }

    public function delete(Comment $comment)
    {
        $comment->delete();
        session()->flash('msg', 'Comentário removido!');
    }

    #[Computed]
    public function comments()
    {
        return Comment::query()
            ->with('product')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('body', 'like', "%{$this->search}%")
                ->orWhereHas('product', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))))
            ->latest()
            ->get();
    }
};
?>

<article class="mx-auto max-w-5xl overflow-x-scroll ">


    <header class="mb-3 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
                {{-- heroicons: chat-bubble-left-right --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            </span>
            <h1 class="text-xl font-semibold text-gray-800">Comentários</h1>
        </div>
    </header>

    <section class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-3">

        {{-- Card 1: total --}}
        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total de comentários</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->total }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-gray-600">
                {{-- heroicons: chat-bubble-left-right --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            </span>
        </div>

        {{-- Card 2: este mês --}}
        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Este mês</p>
                <p class="mt-2 text-2xl font-semibold text-green-700">{{ $this->comentariosDoMes }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-green-50 text-green-700">
                {{-- heroicons: calendar --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </span>
        </div>

        {{-- Card 3: mais comentados --}}
        <div class="rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Mais comentados</p>
                <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
                    {{-- heroicons: chart-bar --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </span>
            </div>
            <ol class="mt-2 space-y-1 text-sm">
                @forelse ($this->produtosMaisComentados as $product)
                    <li wire:key="top-product-{{ $product->id }}" class="flex items-center justify-between gap-2">
                        <span class="truncate text-gray-800">{{ $loop->iteration }}. {{ $product->name }}</span>
                        <span class="inline-flex shrink-0 items-center gap-1 font-medium text-gray-600">
                            {{-- heroicons: chat-bubble-left --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                            </svg>
                            {{ $product->comments_count }}
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500">Ainda sem comentários.</li>
                @endforelse
            </ol>
        </div>

    </section>

    @if(session('msg'))
        <p class="mb-3 flex items-center gap-2 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </p>
    @endif

    <div class="relative mb-3">
        {{-- heroicons: magnifying-glass --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por comentário ou produto..."
            class="w-full rounded-sm border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
    </div>


    <section class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">

        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Comentário</th>
                    <th class="px-3 py-2 font-medium">comentarios</th>
                    <th class="px-3 py-2 font-medium">Produto</th>
                    <th class="px-3 py-2 font-medium">Criado em</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse ($this->comments as $comment)

                    <tr wire:key="comment-{{ $comment->id }}" class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-800">
                            <div class="flex items-start gap-2">
                                {{-- heroicons: chat-bubble-left --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                </svg>
                                <span>{{ Str::limit($comment->body, 80) }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-gray-800">{{ $comment->count() }}</td>
                        <td class="px-3 py-2 text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: cube --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                {{ $comment->product?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $comment->created_at?->format('d/m/Y') }}
                            </span>
                        </td>

                        {{-- Acções --}}
                        <td class="px-3 py-2">
                            <div class="flex items-center justify-end gap-1">

                                {{-- Abre a página do produto onde o comentário foi feito --}}
                                @if ($comment->product?->status === 'active')
                                    <a href="{{ route('public.products.show', $comment->product) }}"
                                        title="Ver na loja" aria-label="Ver na loja"
                                        class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-200 text-blue-600 hover:border-blue-200 hover:bg-blue-50">
                                        {{-- heroicons: arrow-top-right-on-square --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </a>
                                @elseif ($comment->product)
                                    <a href="{{ route('products.edit', $comment->product) }}" wire:navigate
                                        title="Produto inativo: abrir no admin" aria-label="Produto inativo: abrir no admin"
                                        class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-200 text-gray-500 hover:bg-gray-50">
                                        {{-- heroicons: eye-slash --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </a>
                                @else
                                    <span class="px-2 text-gray-300">—</span>
                                @endif

                                {{-- Remover --}}
                                <button wire:click="delete({{ $comment->id }})"
                                    wire:confirm="Tens a certeza que queres remover este comentário?"
                                    title="Remover comentário" aria-label="Remover comentário"
                                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-sm border border-gray-200 text-red-600 hover:border-red-200 hover:bg-red-50">
                                    {{-- heroicons: trash --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                {{-- heroicons: chat-bubble-left --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                </svg>
                                Nenhum comentário encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

</article>