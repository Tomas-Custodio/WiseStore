<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component
{
    public string $search = '';

    public function total()
    {
        return Product::count();
    }

    public function ativos()
    {
        return Product::where('status', 'active')->count();
    }

    public function inativos()
    {
        return Product::where('status', 'inactive')->count();
    }

    public function delete(Product $product)
    {
        $product->delete();
        session()->flash('msg', 'Produto removido!');
    }

    public function products()
    {
        return Product::query()
            ->with(['user', 'category'])
            ->withCount('comments')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->latest()
            ->get();
    }
};
?>

<article class="mx-auto max-w-5xl">

    <header class="mb-3 flex items-center justify-between gap-3">

        <div class="flex items-center gap-3">

            <span class="flex size-10 items-center justify-center rounded-sm bg-blue-50 text-blue-600">

                {{-- heroicons: cube --}}

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </span>
            <h1 class="text-xl font-semibold text-gray-800">Produtos</h1>
        </div>

        @auth
            <a href="{{ route('products.create') }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-sm bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                {{-- heroicons: plus --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Novo produto
            </a>
        @endauth

    </header>

    {{-- Cards separados --}}
    <section class="mb-3 grid grid-cols-3 gap-3">

        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total de produtos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->total() }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-gray-600">
                {{-- heroicons: cube --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </span>
        </div>

        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Ativos</p>
                <p class="mt-2 text-2xl font-semibold text-green-700">{{ $this->ativos() }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-green-50 text-green-700">
                {{-- heroicons: check-circle --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
        </div>

        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Inativos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-500">{{ $this->inativos() }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-gray-500">
                {{-- heroicons: x-circle --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
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
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome ou descrição..."
            class="w-full rounded-sm border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
    </div>

    <div class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">

        <table class="w-full text-left text-sm">

            <thead class="bg-gray-50 text-gray-600">

                <tr>
                    <th class="px-3 py-2 font-medium">Nome</th>
                    <th class="px-3 py-2 font-medium">Categoria</th>
                    <th class="px-3 py-2 font-medium">Dono</th>
                    <th class="px-3 py-2 font-medium">Estado</th>
                    <th class="px-3 py-2 font-medium">Promoção</th>
                    <th class="px-3 py-2 font-medium">Comentários</th>
                    <th class="px-3 py-2 font-medium">Criado em</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>

            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse ($this->products() as $product)

                    <tr wire:key="product-{{ $product->id }}" class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <div class="flex items-start gap-2">
                                {{-- heroicons: cube --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mt-0.5 size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                <div>
                                    <p class="text-gray-800">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($product->description, 60) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: tag --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                </svg>
                                {{ $product->category?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: user-circle --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{ $product->user?->name ?? '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <span @class([
                                'inline-flex items-center gap-1 rounded-sm px-2 py-1 text-xs font-medium',
                                'bg-green-50 text-green-700' => $product->status === 'active',
                                'bg-gray-100 text-gray-600' => $product->status !== 'active',
                            ])>
                                @if ($product->status === 'active')
                                    {{-- heroicons: check-circle --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @else
                                    {{-- heroicons: x-circle --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                @endif
                                {{ $product->status === 'active' ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            @if ($product->promotion)
                                <span class="inline-flex items-center gap-1 rounded-sm bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                    {{-- heroicons: tag --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                    </svg>
                                    Sim
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: chat-bubble-left --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.076-4.076a1.526 1.526 0 0 1 1.037-.443 48.282 48.282 0 0 0 5.68-.494c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                                </svg>
                                {{ $product->comments_count }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $product->created_at?->format('d/m/Y') }}
                            </span>
                        </td>

                        {{-- Acções --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <div class="flex items-center justify-end gap-1">

                                <a href="{{ route('products.edit', $product) }}" wire:navigate
                                    title="Editar produto" aria-label="Editar produto"
                                    class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-200 text-blue-600 hover:border-blue-200 hover:bg-blue-50">
                                    {{-- heroicons: pencil-square --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>

                                <button wire:click="delete({{ $product->id }})"
                                    wire:confirm="Tens a certeza que queres remover este produto?"
                                    title="Remover produto" aria-label="Remover produto"
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
                        <td colspan="8" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                {{-- heroicons: inbox --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                                Nenhum produto encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</article>