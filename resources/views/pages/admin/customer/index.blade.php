<?php

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Clientes')] class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]

    public string $search = '';

    # funcao para actualizar o campo de pesquisa 

    public function updatedSearch(): void {

        $this->resetPage();
    }

    /**
     * @return array{total: int, newThisMonth: int, blocked: int}
     */
    #[Computed]

    /* uma funcao que retrona um array como resposta que pode ser 
            usado normalmente no blade na sua forma original    que um array $this->test['total']; */

    public function stats(): array {

        return [
            'total' => User::customers()->count(),
            'newThisMonth' => User::customers()->where('created_at', '>=', now()->startOfMonth())->count(),
            'blocked' => User::customers()->where('status', 'blocked')->count(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, User>
     * 
     */
    #[Computed]

    # costumers : uma funcao que pega todos os clientes do sistema e a resposta deve ser uma pagncao de clientes )

    public function customers(): LengthAwarePaginator {

        return User::customers()

            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))->latest()->paginate(15);
    }


   # funcao para bloquear usuario

    public function toggleBlock(int $customerId): void {

        $customer = User::customers()->findOrFail($customerId);

        $customer->forceFill(['status' => $customer->isBlocked() ? 'free' : 'blocked'])->save();

        session()->flash('msg', $customer->isBlocked() ? 'Cliente bloqueado.' : 'Cliente desbloqueado.');
    }

}; ?>

<article class="mx-auto max-w-5xl">

    <!---Header dos cleintes --->

    <header class="mb-3 flex items-center gap-3">

        <span class="flex size-10 shrink-0 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
            {{-- heroicons: users --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
        </span>

        <div>
            <h1 class="text-xl font-semibold text-gray-800">Clientes</h1>

            <p class="text-sm text-gray-500">Os clientes registam-se sozinhos na loja. Aqui 
                podes consultá-los e bloquear contas.
            </p>
        </div>

    </header>

    <!---sessao de quadros detalhados sobre quantias e novos cadatrados  --->

    <section class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total de clientes</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->stats['total'] }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-gray-600">
                {{-- heroicons: users --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </span>
        </section>

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Novos este mês</p>
                <p class="mt-2 text-2xl font-semibold text-green-700">{{ $this->stats['newThisMonth'] }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-green-50 text-green-700">
                {{-- heroicons: user-plus --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
            </span>
        </section>

        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Bloqueados</p>
                <p class="mt-2 text-2xl font-semibold text-red-700">{{ $this->stats['blocked'] }}</p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-red-50 text-red-700">
                {{-- heroicons: no-symbol --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
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
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome ou email..."
            class="w-full rounded-sm border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
    </div>

    <div class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">

        <table class="w-full text-left text-sm">

            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Nome</th>
                    <th class="px-3 py-2 font-medium">Email</th>
                    <th class="px-3 py-2 font-medium">Registado em</th>
                    <th class="px-3 py-2 font-medium">Estado</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($this->customers as $customer)
                    <tr wire:key="customer-{{ $customer->id }}" class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-800">
                            <span class="inline-flex items-center gap-2">
                                {{-- heroicons: user-circle --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 shrink-0 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                {{ $customer->name }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-600">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: envelope --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                                {{ $customer->email }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $customer->created_at?->format('d/m/Y') }}
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            @if ($customer->isBlocked())
                                <span class="inline-flex items-center gap-1 rounded-sm bg-red-50 px-2 py-1 text-xs font-medium text-red-700">
                                    {{-- heroicons: no-symbol --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    Bloqueado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-sm bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    {{-- heroicons: check-circle --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    Ativo
                                </span>
                            @endif
                        </td>

                        {{-- Acções --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <div class="flex items-center justify-end gap-1">

                                @if ($customer->isBlocked())
                                    <button wire:click="toggleBlock({{ $customer->id }})"
                                        title="Desbloquear cliente" aria-label="Desbloquear cliente"
                                        class="inline-flex size-8 cursor-pointer items-center justify-center rounded-sm border border-gray-200 text-blue-600 hover:border-blue-200 hover:bg-blue-50">
                                        {{-- heroicons: lock-open --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 1 9 0v3.75M3.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H3.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                    </button>
                                @else
                                    <button wire:click="toggleBlock({{ $customer->id }})"
                                        wire:confirm="Bloquear este cliente? Ele deixa de conseguir entrar na loja."
                                        title="Bloquear cliente" aria-label="Bloquear cliente"
                                        class="inline-flex size-8 cursor-pointer items-center justify-center rounded-sm border border-gray-200 text-red-600 hover:border-red-200 hover:bg-red-50">
                                        {{-- heroicons: lock-closed --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                        </svg>
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                {{-- heroicons: inbox --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                                Nenhum cliente encontrado.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</article>