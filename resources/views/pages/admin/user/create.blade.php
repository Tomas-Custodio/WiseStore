<?php

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = Hash::make($data['password']);

        $admin = new User($data);
        $admin->role = 'admin';
        $admin->save();

        $this->reset();
        session()->flash('msg', 'Administrador adicionado à equipa!');
    }
}; ?>

<article class="mx-auto max-w-3xl px-4 py-10">

    {{-- Voltar --}}
    <a href="{{ route('users.index') }}" wire:navigate
        class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-blue-600">

        {{-- heroicons: arrow-left --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Voltar à equipa
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

                {{-- heroicons: shield-check --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>

            <div>
                <h1 class="text-xl font-bold text-gray-900">Novo administrador</h1>
                <p class="mt-0.5 text-sm text-gray-500">Dê acesso à área de gestão a um novo membro da equipa.</p>
            </div>
        </header>

        {{-- Campos --}}
        <div class="space-y-6 px-6 py-6">

            {{-- Nome e email lado a lado --}}
            <div class="grid gap-6 sm:grid-cols-2">

                {{-- Nome --}}
                <section class="space-y-1.5">
                    <label for="name" class="block text-sm font-semibold text-gray-700">
                        Nome <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                        {{-- heroicons: user --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </div>

                        <input wire:model="name" id="name" type="text" autocomplete="name" placeholder="Ex.: Ana Silva"
                        @class([
                            'pr-3 w-full rounded-sm border py-2.5 pl-10 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
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

                {{-- Email --}}
                <section class="space-y-1.5">
                    <label for="email" class="block text-sm font-semibold text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                        {{-- heroicons: envelope --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>

                        <input wire:model="email" id="email" type="email" autocomplete="email" placeholder="nome@exemplo.com"
                        @class([
                            'pr-3 w-full rounded-sm border py-2.5 pl-10 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('email'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('email'),
                        ]) />
                    </div>

                @error('email')
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

            {{-- Password com botão mostrar/esconder (Alpine) --}}
            <section x-data="{ show: false }" class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-gray-700">
                    Password <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                        {{-- heroicons: lock-closed --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </div>

                    <input wire:model="password" id="password" x-bind:type="show ? 'text' : 'password'" type="password" autocomplete="new-password" placeholder="Mínimo 8 caracteres"
                        @class([
                            'pr-11 w-full rounded-sm border py-2.5 pl-10 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('password'),
                            'border-gray-300 focus:border-blue-500 focus:ring-blue-100' => ! $errors->has('password'),
                        ]) />

                    <button type="button" x-on:click="show = ! show"
                        x-bind:aria-label="show ? 'Esconder password' : 'Mostrar password'"
                        class="absolute inset-y-0 right-0 flex cursor-pointer items-center px-3 text-gray-400 transition hover:text-gray-600">

                        {{-- heroicons: eye --}}
                        <svg x-show="! show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>

                        {{-- heroicons: eye-slash --}}
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>

                @error('password')
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

            <a href="{{ route('users.index') }}" wire:navigate
                class="inline-flex items-center justify-center rounded-sm border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100">
                Cancelar
            </a>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-sm bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">

                {{-- heroicons: user-plus (escondido durante o envio) --}}
                <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>

                {{-- heroicons: arrow-path (a girar durante o envio) --}}
                <svg wire:loading wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>

                Adicionar à equipa
            </button>

        </footer>

    </form>

</article>
