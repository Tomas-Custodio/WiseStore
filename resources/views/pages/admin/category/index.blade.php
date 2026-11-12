<?php

use Livewire\Component;
use App\Models\Category;

new class extends Component
{
    public string $search = '';

    public function total()
    {
        return Category::count();
    }

    public function comProdutos()
    {
        return Category::has('products')->count();
    }

    public function semProdutos()
    {
        return Category::doesntHave('products')->count();
    }

    public function delete(Category $category)
    {
        $category->delete();
        session()->flash('msg', 'Categoria removida!');
    }

    public function categories()
    {
        return Category::query()
            ->withCount('products')
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->get();
    }
};
?>

<article class="mx-auto max-w-5xl">

    <!-- cabecalho de categorias --->

    <header class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
                {{-- heroicons: squares-2x2 --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </span>

            <h1 class="text-xl font-semibold text-gray-800">Categorias</h1>
        </div>

        <!---add uma nova categoria ( atalho ) ---->

        <a href="{{ route('categories.create') }}" wire:navigate
            class="flex items-center gap-2 rounded-sm bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
            {{-- heroicons: plus --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova categoria
        </a>

    </header>

    <!--- container maximo para detalhes delicados sobre a actegoria ! ---->

    <section class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">

        <!--- contador de total de categorias ---->

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total de categorias</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->total() }}</p>
            </div>
            <span class="flex size-9 items-center justify-center rounded-sm bg-gray-100 text-gray-600">
                {{-- heroicons: tag --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
            </span>
        </section>

        <!--- contador de de categroias que estao ligados aos produtos ---->

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Com produtos</p>
                <p class="mt-2 text-2xl font-semibold text-green-700">{{ $this->comProdutos() }}</p>
            </div>
            <span class="flex size-9 items-center justify-center rounded-sm bg-green-50 text-green-700">
                {{-- heroicons: cube --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </span>
        </section>

         <!--- contador de de categroias que nao estao ligados a um produtos ---->

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Vazias</p>
                <p class="mt-2 text-2xl font-semibold text-gray-500">{{ $this->semProdutos() }}</p>
            </div>
            <span class="flex size-9 items-center justify-center rounded-sm bg-gray-100 text-gray-500">
                {{-- heroicons: inbox --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                </svg>
            </span>
        </section>

    </section>

    {{-- <!--- Msg do sistema ---> --}}

    @if(session('msg'))

        <p class="mb-3 flex items-center gap-2 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </p>

    @endif

    {{-- <!--- campo de pesquisa de categorias ---> --}}

    <section class="relative mb-3">
        {{-- heroicons: magnifying-glass --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome ou descrição..."
            class="w-full rounded-sm border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
    </section>

    {{-- <!--- tabela para apresentacao & manipulacao de categorias em listas  ---> --}}

    <section class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">

        <table class="w-full text-left text-sm">

            {{-- <!--- cabecalho do sistema ---> --}}

            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Nome</th>
                    <th class="px-3 py-2 font-medium">Produtos</th>
                    <th class="px-3 py-2 font-medium">Criada em</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>
            </thead>


            {{-- <!--- corpo da tabela e suas manipulacoes ---> --}}

            <tbody class="divide-y divide-gray-100">

                @forelse ($this->categories() as $category)
                    <tr wire:key="category-{{ $category->id }}" class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
                                    {{-- heroicons: tag --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-gray-800">{{ $category->name }}</p>
                                    <p class="text-xs text-gray-500">{{ Str::limit($category->description, 80) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: cube --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                {{ $category->products_count }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $category->created_at?->format('d/m/Y') }}
                            </span>
                        </td>

                        {{-- Acções --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <div class="flex items-center justify-end gap-1">

                                <a href="{{ route('categories.edit', $category) }}" wire:navigate
                                    title="Editar categoria" aria-label="Editar categoria"
                                    class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-200 text-blue-600 hover:border-blue-200 hover:bg-blue-50">
                                    {{-- heroicons: pencil-square --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>

                                <button wire:click="delete({{ $category->id }})"
                                    wire:confirm="Tens a certeza que queres remover esta categoria? Os produtos ficam sem categoria."
                                    title="Remover categoria" aria-label="Remover categoria"
                                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-sm border border-gray-200 text-red-600 hover:border-red-200 hover:bg-red-50">
                                    {{-- heroicons: trash --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>

            {{-- <!--- resposta do sistema quano esta sem categorias / 
                ou quando nao acha uma categoria especifica que foi pesquisada  ---> --}}
                

                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                {{-- heroicons: inbox --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                                Nenhuma categoria encontrada.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </section>

</article>