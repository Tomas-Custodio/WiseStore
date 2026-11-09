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
};
?>

<div class="mx-auto max-w-5xl px-4 py-6">

    {{-- Cabeçalho --}}
    
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900">Minhas Encomendas</h1>
        <p class="mt-0.5 text-sm text-gray-500">{{ $this->encomendas->count() }} encomenda(s)</p>
    </div>

    @forelse ($this->encomendas as $encomenda)

        <div wire:key="encomenda-{{ $encomenda->id }}" class="mb-4 overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

            {{-- Dados da encomenda (tabela orders) --}}
            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Encomenda #{{ $encomenda->id }}</p>
                    <p class="text-xs text-gray-500">{{ $encomenda->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="rounded-sm bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-700">
                    {{ $encomenda->status }}
                </span>
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
                            <td class="px-4 py-2 text-right text-sm text-gray-600">{{ number_format($item->preco, 0, ',', ' ') }} Kz</td>
                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900">{{ number_format($item->preco * $item->quantity, 0, ',', ' ') }} Kz</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Total --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-4 py-3">
                <span class="text-sm font-medium text-gray-500">Total</span>
                <span class="text-lg font-bold text-gray-900">{{ number_format($encomenda->total, 0, ',', ' ') }} Kz</span>
            </div>

        </div>

    @empty

        <div class="rounded-sm border border-dashed border-gray-300 bg-white p-8 text-center">
            <h2 class="text-base font-semibold text-gray-800">Ainda não fez nenhuma encomenda</h2>
        </div>

    @endforelse

</div>
