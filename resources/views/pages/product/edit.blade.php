<?php

use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

new class extends Component
{
    public Product $product;

    public string $name = '';
    public string $preco = '';
    public string $description = '';
    public string $status = 'active';
    public bool $promotion = false;
    public ?int $user_id = null;
    public ?int $category_id = null;

    public function mount(Product $product)
    {
        $this->product = $product;
        $this->preco = (string) $product->preco;
        $this->fill($product->only(['name', 'description', 'status', 'promotion', 'user_id', 'category_id']));
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function users()
    {
        return User::orderBy('name')->get(['id', 'name']);
    }

    public function save()
    {
        $data = $this->validate([
            'user_id' => 'required|exists:users,id',
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
            'promotion' => 'boolean',
        ]);

        $this->product->update($data);
        session()->flash('msg', 'Produto atualizado!');
    }
};
?>

<article class="mx-auto max-w-3xl px-4 py-10">

    {{-- Voltar --}}
    <a href="{{ route('products.index') }}" wire:navigate
        class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-blue-600">

        {{-- heroicons: arrow-left --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        Voltar aos produtos
    </a>

    {{-- Mensagem de sucesso --}}
    @if (session('msg'))
        <div class="mb-6 flex items-center gap-3 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">

            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            {{ session('msg') }}
        </div>
    @endif

    {{-- Card do formulário --}}
    <form wire:submit="save" class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

        {{-- Cabeçalho do card --}}
        <header class="flex items-center gap-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white px-6 py-5">
            <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-gray-200 bg-white text-gray-300 shadow-sm">
                @if ($product->img)
                    <img src="{{ asset('storage/' . $product->img) }}" alt="{{ $product->name }}" class="size-full object-cover" />
                @else
                    {{-- heroicons: photo --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                @endif
            </div>

            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-900">Editar produto</h1>
                <p class="mt-0.5 truncate text-sm text-gray-500">A alterar “{{ $product->name }}”.</p>
            </div>
        </header>

        {{-- Campos --}}
        <div class="space-y-6 px-6 py-6">

            {{-- Nome --}}
            <section class="space-y-1.5">
                <label for="name" class="block text-sm font-semibold text-gray-700">Nome <span class="text-red-500">*</span></label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        {{-- heroicons: pencil-square --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                    </div>

                    <input wire:model="name" id="name" type="text" placeholder="Ex.: Galaxy S24"
                        @class([
                            'w-full rounded-sm border bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('name'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('name'),
                        ]) />
                </div>

                @error('name')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <div class="grid gap-6 sm:grid-cols-2">

            {{-- Preço (Kz) --}}
            <section class="space-y-1.5">
                <label for="preco" class="block text-sm font-semibold text-gray-700">Preço (Kz) <span class="text-red-500">*</span></label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        {{-- heroicons: banknotes --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                    </div>

                    <input wire:model="preco" id="preco" type="number" min="0" step="0.01" placeholder="0,00"
                        @class([
                            'w-full rounded-sm border bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('preco'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('preco'),
                        ]) />
                </div>

                @error('preco')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Categoria --}}
            <section class="space-y-1.5">
                <label for="category_id" class="block text-sm font-semibold text-gray-700">Categoria</label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        {{-- heroicons: tag --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                    </div>

                    <select wire:model="category_id" id="category_id"
                        @class([
                            'w-full rounded-sm border bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('category_id'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('category_id'),
                        ])>
                        <option value="">Sem categoria</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                @error('category_id')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            </div>

            {{-- Descrição --}}
            <section class="space-y-1.5">
                <label for="description" class="block text-sm font-semibold text-gray-700">Descrição <span class="text-red-500">*</span></label>

                <div class="relative">
                    <div class="pointer-events-none absolute left-0 top-0 flex pl-3 pt-2.5 text-gray-400">
                        {{-- heroicons: document-text --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    </div>

                    <textarea wire:model="description" id="description" rows="5" placeholder="Descreva o produto..."
                        @class([
                            'w-full rounded-sm border bg-white py-2.5 pl-10 pr-3 resize-none text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('description'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('description'),
                        ])></textarea>
                </div>

                @error('description')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            <div class="grid gap-6 sm:grid-cols-2">

            {{-- Dono --}}
            <section class="space-y-1.5">
                <label for="user_id" class="block text-sm font-semibold text-gray-700">Dono <span class="text-red-500">*</span></label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        {{-- heroicons: user --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </div>

                    <select wire:model="user_id" id="user_id"
                        @class([
                            'w-full rounded-sm border bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('user_id'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('user_id'),
                        ])>
                        <option value="">Seleciona um utilizador</option>
                        @foreach ($this->users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                @error('user_id')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Estado --}}
            <section class="space-y-1.5">
                <label for="status" class="block text-sm font-semibold text-gray-700">Estado</label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        {{-- heroicons: eye --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    </div>

                    <select wire:model="status" id="status"
                        @class([
                            'w-full rounded-sm border bg-white py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('status'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('status'),
                        ])>
                        <option value="active">Ativo</option>
                        <option value="inactive">Inativo</option>
                    </select>
                </div>

                @error('status')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            </div>

                {{-- Promoção --}}
                <section class="space-y-1.5">
                    <span class="block text-sm font-semibold text-gray-700">Promoção</span>

                    <label class="flex cursor-pointer items-center gap-3 rounded-sm border border-gray-300 px-3 py-2.5 transition hover:bg-gray-50 has-[:checked]:border-amber-400 has-[:checked]:bg-amber-50">
                        <input wire:model="promotion" type="checkbox" class="size-4 rounded-sm border-gray-300 text-amber-500 focus:ring-amber-400" />

                        {{-- heroicons: fire --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-amber-500"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>

                        <span class="text-sm text-gray-700">Em promoção</span>
                    </label>
                </section>

        </div>

        {{-- Rodapé com ações --}}
        <footer class="flex flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-end">

            <a href="{{ route('products.index') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-sm border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                Cancelar
            </a>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-sm bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">

                {{-- heroicons: check (escondido durante o envio) --}}
                <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>

                {{-- heroicons: arrow-path (a girar durante o envio) --}}
                <svg wire:loading wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 animate-spin"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>

                Guardar alterações
            </button>

        </footer>

    </form>

</article>
