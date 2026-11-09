<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Entrar')] class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // 1. Confirma email e password sem iniciar sessão.

        if (! Auth::validate($credentials)) {
            $this->reset('password');
            $this->addError('email', 'Email ou password incorretos.');

            return;
        }

        $user = Auth::getLastAttempted();

        // 2. Credenciais certas, mas a conta foi bloqueada por um administrador.

        if ($user->isBlocked()) {
            $this->reset('password');
            $this->addError('email', 'A tua conta está bloqueada. Contacta a loja.');

            return;
        }

        // 3. Tudo certo: inicia a sessão.

        Auth::login($user, $this->remember);

        session()->regenerate();

        // Admin vai para o dashboard; cliente vai para o início da loja.

        if (Auth::user()->isAdmin()) {
            $this->redirectRoute('dashboard', navigate: true);

            return;
        }

        $this->redirectRoute('home', navigate: true);
    }
}; ?>

<article class="rounded-sm border border-gray-200 bg-white p-6 shadow-sm">

    <h1 class="mb-4 text-xl font-semibold text-gray-800">Entrar</h1>

    <form wire:submit="login" class="space-y-3">

        <div class="space-y-1">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input wire:model="email" id="email" type="email" autocomplete="email" autofocus
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            @error('email') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="space-y-1">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input wire:model="password" id="password" type="password" autocomplete="current-password"
                class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
            @error('password') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input wire:model="remember" type="checkbox" class="rounded-sm border-gray-300" />
            Lembrar-me
        </label>

        <button type="submit"
            class="w-full cursor-pointer rounded-sm bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
            wire:loading.attr="disabled">
            Entrar
        </button>

    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
        Ainda não tens conta?
        <a href="{{ route('public.users.create') }}" wire:navigate class="text-blue-600 hover:underline">Criar conta</a>
    </p>

</article>
