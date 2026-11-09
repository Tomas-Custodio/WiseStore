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

<article class="mx-auto max-w-xl space-y-4 py-3">

    <header>
        <h1 class="text-xl font-semibold text-gray-800">O meu perfil</h1>
        <p class="text-sm text-gray-500">Cliente desde {{ auth()->user()->created_at?->format('d/m/Y') }}</p>
    </header>

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

</article>