<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    /**
     * Itens do carrinho do utilizador autenticado.
     */
    #[Computed]
    public function carts()
    {
        return auth()->user()->carts()->with('product')->latest()->get();
    }

    /**
     * Diminui 1 unidade de uma linha do carrinho.
     */
    public function decrease(int $cartId): void
    {
        $cart = auth()->user()->carts()->findOrFail($cartId);

        if ($cart->quantity > 1) {
            $cart->quantity -= 1;
            $cart->save();
        }
    }

    /**
     * Aumenta 1 unidade de uma linha do carrinho (máximo 99).
     */
    public function increase(int $cartId): void
    {
        $cart = auth()->user()->carts()->findOrFail($cartId);

        if ($cart->quantity < 99) {
            $cart->quantity += 1;
            $cart->save();
        }
    }

    /**
     * Remove uma linha do carrinho.
     */
    public function remove(int $cartId): void
    {
        $cart = auth()->user()->carts()->findOrFail($cartId);

        $cart->delete();
    }

    /**
     * Transforma o carrinho numa encomenda.
     *
     * Copia o nome e o preço atuais de cada produto para order_items,
     * para que a encomenda não mude se o produto for alterado depois.
     */
    public function checkout(): void {
        
        $carts = auth()->user()->carts()
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->with('product')
            ->get();

        if ($carts->isEmpty()) {
            session()->flash('checkout-error', 'Não há produtos disponíveis no carrinho.');

            return;
        }

        DB::transaction(function () use ($carts) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $carts->sum(fn ($cart) => $cart->product->preco * $cart->quantity),
                'status' => 'pending',
            ]);

            foreach ($carts as $cart) {
                $order->items()->create([
                    'product_id' => $cart->product_id,
                    'product_name' => $cart->product->name,
                    'preco' => $cart->product->preco,
                    'quantity' => $cart->quantity,
                ]);
            }

            auth()->user()->carts()->delete();
        });

        unset($this->carts);

        session()->flash('checkout', 'Encomenda feita com sucesso!');
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-6">

    {{-- Cabeçalho --}}
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Meu Carrinho</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ $this->carts->count() }} produto(s) no carrinho</p>
        </div>

         <section class="flex- items-center justify-around gap-2">
             
                     <a href="{{ route('public.encomendas.index') }}"
                wire:navigate
                class="rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100">
                Ver encomendas
                     </a>
         </section>
    </div>

    @if (session('checkout'))
        <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{ session('checkout') }}
        </div>
    @endif

    @if (session('checkout-error'))
        <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            {{ session('checkout-error') }}
        </div>
    @endif

    @if ($this->carts->isEmpty())

        {{-- Carrinho vazio --}}
        <div class="rounded-sm border border-dashed border-gray-300 bg-white p-8 text-center">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-sm bg-gray-100 text-2xl">🛒</div>
            <h2 class="text-base font-semibold text-gray-800">O seu carrinho está vazio</h2>
            <p class="mt-1 text-sm text-gray-500">Adicione produtos para vê-los aqui.</p>
        </div>

    @else

        <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Produto</th>
                            <th class="px-4 py-3 text-center">Quantidade</th>
                            <th class="px-4 py-3 text-right">Preço</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3"><span class="sr-only">Remover</span></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">

                        @foreach ($this->carts as $cart)
                            <tr wire:key="cart-{{ $cart->id }}" class="transition hover:bg-gray-50">

                                {{-- Produto --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($cart->product?->img)
                                            <img
                                                src="{{ asset('storage/' . $cart->product->img) }}"
                                                alt="{{ $cart->product->name }}"
                                                class="h-12 w-12 rounded-sm object-cover ring-1 ring-gray-200">
                                        @else
                                            <div class="flex h-12 w-12 items-center justify-center rounded-sm bg-gray-100 text-lg">📦</div>
                                        @endif

                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $cart->product?->name ?? 'Produto removido' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Quantidade com botões de diminuir e aumentar --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center">
                                        <div class="inline-flex items-center rounded-sm border border-gray-300">

                                            {{-- Diminuir --}}
                                            <button
                                                type="button"
                                                wire:click="decrease({{ $cart->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="decrease({{ $cart->id }}), increase({{ $cart->id }}), remove({{ $cart->id }})"
                                                @disabled($cart->quantity <= 1)
                                                aria-label="Diminuir quantidade"
                                                title="Diminuir quantidade"
                                                class="px-2 py-1.5 text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">

                                                {{-- heroicons: minus --}}
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                    class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                                </svg>
                                            </button>

                                            <span class="min-w-8 px-1 text-center text-sm font-semibold text-gray-800">
                                                {{ $cart->quantity }}
                                            </span>

                                            {{-- Aumentar --}}
                                            <button
                                                type="button"
                                                wire:click="increase({{ $cart->id }})"
                                                wire:loading.attr="disabled"
                                                wire:target="decrease({{ $cart->id }}), increase({{ $cart->id }}), remove({{ $cart->id }})"
                                                @disabled($cart->quantity >= 99)
                                                aria-label="Aumentar quantidade"
                                                title="Aumentar quantidade"
                                                class="px-2 py-1.5 text-gray-600 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40">

                                                {{-- heroicons: plus --}}
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                    stroke-width="1.5"
                                                    stroke="currentColor"
                                                    class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                            </button>

                                        </div>
                                    </div>
                                </td>

                                {{-- Preço --}}
                                <td class="px-4 py-3 text-right text-sm text-gray-600">
                                    {{ $cart->product?->formattedPrice() }}
                                </td>

                                {{-- Subtotal --}}
                                <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                    {{ number_format($cart->product?->preco * $cart->quantity, 0, ',', ' ') }} Kz
                                </td>

                                {{-- Remover --}}
                                <td class="px-4 py-3 text-right">
                                    <button
                                        type="button"
                                        wire:click="remove({{ $cart->id }})"
                                        wire:confirm="Remover este produto do carrinho?"
                                        wire:loading.attr="disabled"
                                        wire:target="remove({{ $cart->id }})"
                                        aria-label="Remover do carrinho"
                                        title="Remover do carrinho"
                                        class="rounded-sm p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 disabled:opacity-40">

                                        {{-- heroicons: trash --}}
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke-width="1.5"
                                            stroke="currentColor"
                                            class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                            />
                                        </svg>
                                    </button>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Total --}}
            <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-gray-50 px-4 py-3">
                <span class="text-sm font-medium text-gray-500">Total</span>
                <span class="text-xl font-bold text-gray-900">
                    {{ number_format($this->carts->sum(fn ($cart) => $cart->product?->preco * $cart->quantity), 0, ',', ' ') }} Kz
                </span>
            </div>

            <div class="flex items-center justify-end p-3">

                <button
                    type="button"
                    wire:click="checkout"
                    wire:confirm="Confirmar a encomenda?"
                    wire:loading.attr="disabled"
                    wire:target="checkout"
                    class="bg-green-400 p-2 rounded-sm hover:text-black text-white disabled:opacity-40">
                    check out
                </button>

            </div>

        </div>

    @endif

</div>