<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        User::create($data);
        $this->reset();
        session()->flash('msg', 'Conta criada com sucesso!');
    }
}; ?>

<article class="mx-auto max-w-md py-10">

    <h1 class="mb-3 text-xl font-semibold text-gray-800">Criar conta</h1>

    @if(session('msg'))
        <p class="mb-3 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{ session('msg') }}
        </p>
    @endif

    <form wire:submit="save" class="space-y-3 rounded-sm border border-gray-200 bg-white p-3 shadow-sm">

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

        <div class="space-y-1">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input wire:model="password" id="password" type="password"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            @error('password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar password</label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
        </div>

        <button type="submit"
            class="w-full cursor-pointer rounded-sm bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            wire:loading.attr="disabled">
            Criar conta
        </button>

    </form>

</article>
