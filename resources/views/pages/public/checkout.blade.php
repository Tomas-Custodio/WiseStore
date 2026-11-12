<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public string $paymentMethod = '';

    /**
     * Produtos ativos do carrinho do utilizador (User -> Cart -> CartItem -> Product).
     */
    #[Computed]
    public function carts()
    {
        return auth()->user()
            ->cartItems()
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->with('product')
            ->get();
    }

    #[Computed]
    public function total()
    {
        return $this->carts->sum(
            fn ($cart) => $cart->product->preco * $cart->quantity
        );
    }

    public function finalizar(): void
    {
        $this->validate([

            'paymentMethod' => ['required', Rule::in(Order::PAYMENT_METHODS)],
        ], [
            'paymentMethod.required' => 'Escolha um método de pagamento.',
            'paymentMethod.in' => 'Método de pagamento inválido.',
        ]);

        $carts = auth()->user()
            ->cartItems()
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->with('product')
            ->get();

        if ($carts->isEmpty()) {
            session()->flash('error', 'Não existem produtos disponíveis.');

            return;
        }

        DB::transaction(function () use ($carts) {

            $order = Order::create([
                'user_id' => auth()->id(),
                'total' => $carts->sum(
                    fn ($cart) => $cart->product->preco * $cart->quantity
                ),
                'status' => 'pending',
                'payment_method' => $this->paymentMethod,
            ]);

            foreach ($carts as $cart) {

                $order->items()->create([
                    'product_id' => $cart->product_id,
                    'product_name' => $cart->product->name,
                    'preco' => $cart->product->preco,
                    'quantity' => $cart->quantity,
                ]);
            }

            // Esvazia o carrinho, mas mantém o carrinho do utilizador.
            auth()->user()->cart->items()->delete();
        });

        session()->flash('checkout', 'Encomenda criada com sucesso!');

        $this->redirectRoute('public.encomendas.index', navigate: true);
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-6">

    {{-- Voltar --}}
    <a href="{{ route('public.cart.index') }}"
        x-on:click.prevent="window.history.length > 1 ? window.history.back() : Livewire.navigate($el.href)"
        class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-blue-600">
        {{-- heroicons: arrow-left --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Voltar ao carrinho
    </a>

    {{-- Cabeçalho --}}
    <div class="mb-4 flex items-center gap-3">
        <span class="flex size-10 items-center justify-center rounded-sm bg-green-50 text-green-600">
            {{-- heroicons: shopping-bag --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        </span>

        <div>
            <h1 class="text-2xl font-bold text-gray-900">Finalizar encomenda</h1>
            <p class="mt-0.5 text-sm text-gray-500">Confirme os produtos antes de criar a encomenda.</p>
        </div>
    </div>

    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 rounded-sm border border-red-200 bg-red-50 p-3 text-sm text-red-700">
            {{-- heroicons: exclamation-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">

        {{-- ======================= Resumo da compra ======================= --}}
        <section class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm lg:col-span-2">

            <header class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-4 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Resumo da compra</h2>
                <span class="text-xs text-gray-500">{{ $this->carts->count() }} produto(s)</span>
            </header>

            <ul class="divide-y divide-gray-100">

                @forelse ($this->carts as $cart)
                    <li wire:key="checkout-{{ $cart->id }}" class="flex items-center gap-3 px-4 py-3">

                        @if ($cart->product->img)
                            <img src="{{ asset('storage/' . $cart->product->img) }}" alt="{{ $cart->product->name }}"
                                class="size-12 shrink-0 rounded-sm object-cover ring-1 ring-gray-200">
                        @else
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-lg">📦</div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ $cart->product->name }}</p>
                            <p class="text-xs text-gray-500">
                                {{ $cart->quantity }} unidade(s) × {{ number_format($cart->product->preco, 0, ',', ' ') }} Kz
                            </p>
                        </div>

                        <p class="whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($cart->product->preco * $cart->quantity, 0, ',', ' ') }} Kz
                        </p>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">
                        Não há produtos no carrinho.
                    </li>
                @endforelse

            </ul>
        </section>

        {{-- ======================= Coluna lateral ======================= --}}
        <div class="space-y-4">

            {{-- ======================= Método de pagamento ======================= --}}

            <section class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

                <header class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3">

                    {{-- heroicons: credit-card --}}

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>

                    <h2 class="text-sm font-semibold text-gray-900">Método de pagamento</h2>

                </header>

                <div class="space-y-2 p-4" role="radiogroup">

                    @foreach (Order::PAYMENT_METHODS as $method)

                        <label wire:key="payment-{{ $loop->index }}"

                            class="group flex cursor-pointer items-start gap-3 rounded-sm border p-3 transition

                                {{ $paymentMethod === $method
                                    ? 'border-green-600 bg-green-50 ring-1 ring-green-600'
                                    : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}">

                            <input type="radio" wire:model.live="paymentMethod" value="{{ $method }}"
                                class="mt-0.5 size-4 shrink-0 accent-green-600">

                            <span class="text-sm font-semibold text-gray-900">{{ $method }}</span>
                            
                        </label>
                    @endforeach

                    @error('paymentMethod')
                        <p class="flex items-center gap-1.5 pt-1 text-xs font-medium text-red-600">
                            {{-- heroicons: exclamation-circle --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </section>

            {{-- ======================= Total e confirmação ======================= --}}
            <aside class="h-fit overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

                <header class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                    <h2 class="text-sm font-semibold text-gray-900">Total</h2>
                </header>

                <div class="space-y-4 p-4">

                    <div class="flex items-baseline justify-between">
                        <span class="text-sm font-medium text-gray-600">Total a pagar</span>
                        <span class="text-2xl font-bold text-gray-900">{{ number_format($this->total, 0, ',', ' ') }} Kz</span>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-100 pt-3 text-sm">
                        <span class="text-gray-500">Pagamento</span>
                        <span class="font-medium {{ $paymentMethod ? 'text-gray-900' : 'italic text-gray-400' }}">
                            {{ $paymentMethod ?: 'Não escolhido' }}
                        </span>
                    </div>

                    <button
                        type="button"
                        wire:click="finalizar"
                        wire:confirm="Deseja confirmar esta encomenda?"
                        wire:loading.attr="disabled"
                        wire:target="finalizar"
                        @disabled($this->carts->isEmpty())
                        class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-sm bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50">

                        <span wire:loading.remove wire:target="finalizar">Confirmar encomenda</span>
                        <span wire:loading wire:target="finalizar">A processar...</span>
                    </button>

                    <a href="{{ route('public.cart.index') }}"
                        x-on:click.prevent="window.history.length > 1 ? window.history.back() : Livewire.navigate($el.href)"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-sm border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        {{-- heroicons: arrow-left --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        Voltar
                    </a>

                </div>
            </aside>

        </div>

    </div>

</div>
