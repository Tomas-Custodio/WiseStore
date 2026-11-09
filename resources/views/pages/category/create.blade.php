<?php

use Livewire\Component;
use App\Models\Category;

new class extends Component {

    public string $name = '';
    public string $description = '';

    public function save() {

        $data = $this->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create($data);
        $this->reset();
        session()->flash('msg', 'Categoria criada!');
    }

};?>

<article class="mx-auto max-w-3xl px-4 py-10">

    {{-- Voltar --}}
    <a href="{{ route('categories.index') }}" wire:navigate
        class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-blue-600">

        {{-- heroicons: arrow-left --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Voltar às categorias
    </a>

    {{-- Mensagem de sucesso --}}
    @if (session('msg'))
        <div class="mb-6 flex items-center gap-3 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">

            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </div>
    @endif

    {{-- Card do formulário --}}
    <form wire:submit="save" class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

        {{-- Cabeçalho do card --}}
        <header class="flex items-center gap-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-6 py-5">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-sm bg-blue-600 text-white shadow-sm">

                {{-- heroicons: tag --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
            </div>

            <div>
                <h1 class="text-xl font-bold text-gray-900">Nova categoria</h1>
                <p class="mt-0.5 text-sm text-gray-500">Organize os produtos da loja por categorias.</p>
            </div>
        </header>

        {{-- Campos --}}
        <div class="space-y-6 px-6 py-6">

            {{-- Nome --}}
            <section class="space-y-1.5">
                <label for="name" class="block text-sm font-semibold text-gray-700">
                    Nome <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                        {{-- heroicons: pencil-square --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                    </div>

                    <input wire:model="name" id="name" type="text" placeholder="Ex.: Telemóveis"
                        @class([
                            'w-full rounded-sm border py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('name'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('name'),
                        ]) />
                </div>

                @error('name')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Descrição --}}
            <section class="space-y-1.5">
                <label for="description" class="block text-sm font-semibold text-gray-700">
                    Descrição <span class="font-normal text-gray-400">(opcional)</span>
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute left-0 top-0 flex pl-3 pt-2.5 text-gray-400">

                        {{-- heroicons: document-text --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>

                    <textarea wire:model="description" id="description" rows="5" placeholder="Descreva brevemente esta categoria..."
                        @class([
                            'w-full resize-none rounded-sm border py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('description'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('description'),
                        ])></textarea>
                </div>

                @error('description')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

        </div>

        {{-- Rodapé com ações --}}
        <footer class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">

            <a href="{{ route('categories.index') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-sm border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                Cancelar
            </a>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-sm bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">

                {{-- heroicons: plus (escondido durante o envio) --}}
                <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>

                {{-- heroicons: arrow-path (a girar durante o envio) --}}
                <svg wire:loading wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>

                Criar categoria
            </button>

        </footer>

    </form>

</article>
