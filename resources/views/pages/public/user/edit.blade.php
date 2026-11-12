<?php

use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function saveProfile()
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        auth()->user()->update($data);

        session()->flash('msg', 'Dados atualizados!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update(['password' => Hash::make($this->password)]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        session()->flash('msg-password', 'Password alterada!');
    }
};
?>

<article class="mx-auto max-w-5xl space-y-4 py-3">

    <header>
        <h1 class="text-xl font-semibold text-gray-800">O meu perfil</h1>
        <p class="text-sm text-gray-500">Cliente desde {{ auth()->user()->created_at?->format('d/m/Y') }}</p>
    </header>

    <div class="grid gap-4 lg:grid-cols-5">

    {{-- Nota (à direita no desktop, no topo no telemóvel) --}}

    <aside class="self-start rounded-sm bg-red-400 p-5 text-sm text-white shadow-sm lg:sticky lg:top-4 lg:order-last lg:col-span-2">

        <p class="mb-3 flex items-center gap-2 text-base font-semibold">
            {{-- heroicons: information-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
            Bom saber
        </p>

        <ul class="list-disc space-y-2 pl-5 leading-relaxed">
            <li>Cada cartão é guardado <strong>em separado</strong>: alterar o nome ou o email não mexe na password, e vice-versa.</li>
            <li>Se não escrever uma password nova, a atual <strong>continua válida</strong>.</li>
            <li>A nova password precisa de <strong>8+ caracteres</strong> e da password atual para confirmar.</li>
        </ul>
    </aside>

    {{-- Formulários (à esquerda, 3 de 5 colunas) --}}

    <div class="space-y-4 lg:col-span-3">

    {{-- Dados pessoais --}}
    <form wire:submit="saveProfile" class="space-y-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm">

        <h2 class="text-sm font-semibold text-gray-800">Dados pessoais</h2>

        @if(session('msg'))
            <p class="rounded-sm border border-green-200 bg-green-50 p-2 text-sm text-green-700">{{ session('msg') }}</p>
        @endif

        <div class="space-y-1">

            <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>

            <input wire:model="name" id="name" type="text"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
                
            @error('name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input wire:model="email" id="email" type="email"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            @error('email') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="cursor-pointer rounded-sm bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Guardar dados
            </button>
        </div>

    </form>

    {{-- Password --}}
    <form wire:submit="updatePassword" class="space-y-3 rounded-sm border border-gray-200 bg-white p-4 shadow-sm">

        <h2 class="text-sm font-semibold text-gray-800">Alterar password</h2>

        @if(session('msg-password'))
            <p class="rounded-sm border border-green-200 bg-green-50 p-2 text-sm text-green-700">{{ session('msg-password') }}</p>
        @endif

        <div class="space-y-1">
            <label for="current_password" class="block text-sm font-medium text-gray-700">Password atual</label>
            <input wire:model="current_password" id="current_password" type="password"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            @error('current_password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1">
            <label for="password" class="block text-sm font-medium text-gray-700">Nova password</label>
            <input wire:model="password" id="password" type="password"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            @error('password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar nova password</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="cursor-pointer rounded-sm bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">
                Alterar password
            </button>
        </div>

    </form>

    </div>

    </div>

</article>