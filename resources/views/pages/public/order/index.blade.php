<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    /**
     * Encomendas do utilizador autenticado, com os produtos de cada uma.
     */
    #[Computed]
    public function encomendas()
    {
        return auth()->user()->orders()->with('items')->latest()->get();
    }

    /**
     * Apaga uma encomenda do utilizador autenticado (os itens são apagados em cascata).
     */
    public function delete(int $id)
    {
        auth()->user()->orders()->findOrFail($id)->delete();

        unset($this->encomendas);

        session()->flash('msg', 'Encomenda apagada!');
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-6">

    {{-- Cabeçalho --}}
    
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Minhas Encomendas</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $this->encomendas->count() }} encomenda(s)</p>
    </div>

    {{-- Total de todas as encomendas --}}

    @if ( $this->encomendas->isNotEmpty() )

        <div class="mb-4 flex items-center justify-between rounded-sm border 
            border-green-200 bg-green-50 px-4 py-3">

            <span class="inline-flex items-center gap-2 text-sm 
                font-medium text-green-800">

                {{-- heroicons: wallet --}}

                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"/>

                </svg>
                
                Total que você deve pagar
            </span>
            <span class="text-xl font-bold text-green-800">{{ number_format($this->encomendas->sum('total'), 0, ',', ' ') }} Kz</span>
        </div>
    @endif

    {{-- Mensagem do sistema --}}
    @if (session('msg'))
        <p class="mb-4 flex items-center gap-2 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </p>
    @endif

    @forelse ($this->encomendas as $encomenda)

        <div wire:key="encomenda-{{ $encomenda->id }}" class="mb-4 overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

            {{-- Dados da encomenda (tabela orders) --}}
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Encomenda Nª{{ $encomenda->id }}</p>
                    <p class="text-xs text-gray-500">{{ $encomenda->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <section class="flex items-center gap-2">
                    <span class="rounded-sm bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-700">
                        {{ $encomenda->status }}
                    </span>

                    {{-- Pagar --}}
                    <a href="{{ route('public.payment.index', $encomenda) }}" wire:navigate
                        class="inline-flex items-center gap-1.5 rounded-sm bg-green-600 px-3 py-1.5 text-sm font-semibold text-white transition hover:bg-green-700">
                        {{-- heroicons: banknotes --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                        </svg>
                        Pagar
                    </a>

                    {{-- Apagar --}}
                    <button type="button" wire:click="delete({{ $encomenda->id }})"
                        wire:confirm="Tens a certeza que queres apagar a encomenda Nº{{ $encomenda->id }}?"
                        title="Apagar encomenda" aria-label="Apagar encomenda"
                        class="inline-flex size-8 cursor-pointer items-center justify-center rounded-sm border border-gray-200 bg-white text-red-600 transition hover:border-red-200 hover:bg-red-50">
                        {{-- heroicons: trash --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                    </button>
                </section>
            </div>

            {{-- Produtos da encomenda (tabela order_items) --}}
            <table class="w-full text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Produto</th>
                        <th class="px-4 py-2 text-center">Quantidade</th>
                        <th class="px-4 py-2 text-right">Preço</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach ($encomenda->items as $item)
                        <tr wire:key="item-{{ $item->id }}">
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $item->product_name }}</td>
                            <td class="px-4 py-2 text-center text-sm text-gray-600">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right text-sm text-gray-600">
                                <span class="inline-flex items-center gap-1">
                                    {{-- heroicons: currency-dollar --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ number_format($item->preco, 0, ',', ' ') }} Kz
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900">
                                <span class="inline-flex items-center gap-1">
                                    {{-- heroicons: banknotes --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-green-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                    {{ number_format($item->preco * $item->quantity, 0, ',', ' ') }} Kz
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Total --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-4 py-3">
                <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500">
                    {{-- heroicons: wallet --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3" />
                    </svg>
                    Total
                </span>
                <span class="text-lg font-bold text-gray-900">{{ number_format($encomenda->total, 0, ',', ' ') }} Kz</span>
            </div>

        </div>

    @empty

        <div class="rounded-sm border border-dashed border-gray-300 bg-white p-8 text-center">
            <h2 class="text-base font-semibold text-gray-800">Ainda não fez nenhuma encomenda</h2>
        </div>

    @endforelse

</div>
