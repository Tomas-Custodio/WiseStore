
<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
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

<div class="mx-auto max-w-4xl px-4 py-6">

    <h1 class="mb-6 text-2xl font-bold text-gray-900">
        Finalizar encomenda
    </h1>

    @if (session('error'))
        <div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-600">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-sm border border-gray-200 bg-white">

        <div class="border-b border-gray-100 p-4">
            <h2 class="font-semibold text-gray-800">
                Resumo da compra
            </h2>
        </div>

        <div class="divide-y divide-gray-100">

            @foreach ($this->carts as $cart)
                <div wire:key="checkout-{{ $cart->id }}"
                    class="flex items-center gap-4 p-4">

                    @if ($cart->product->img)
                        <img src="{{ asset('storage/' . $cart->product->img) }}" alt="{{ $cart->product->name }}"
                            class="size-16 shrink-0 rounded-sm object-cover ring-1 ring-gray-200">
                    @else
                        <div class="flex size-16 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-2xl">📦</div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <p class="truncate font-medium text-gray-900">
                            {{ $cart->product->name }}
                        </p>

                        <p class="mt-1 inline-flex items-center gap-1.5 text-sm text-gray-500">
                            {{-- heroicons: cube --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            {{ $cart->quantity }} unidade(s) × {{ number_format($cart->product->preco, 0, ',', ' ') }} Kz
                        </p>
                    </div>

                    <p class="whitespace-nowrap text-sm font-semibold text-gray-800">
                        {{ number_format($cart->product->preco * $cart->quantity, 0, ',', ' ') }} Kz
                    </p>
                </div>
                
            @endforeach

        </div>

        <div class="flex justify-between border-t border-gray-100 bg-gray-50 p-4">
            <span class="font-semibold text-gray-700">
                Total
            </span>

            <span class="text-xl font-bold text-gray-900">
                {{ number_format($this->total, 0, ',', ' ') }} Kz
            </span>
        </div>

    </div>

  <div class="mt-5 flex flex-wrap items-center justify-between gap-3">

    {{-- Volta para a página anterior; se não houver histórico, vai para os produtos. --}}
    <a href="{{ route('public.products.index') }}"
        x-on:click.prevent="window.history.length > 1 ? window.history.back() : Livewire.navigate($el.href)"
        class="inline-flex items-center gap-1.5 rounded-sm border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        {{-- heroicons: arrow-left --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Voltar
    </a>

    @if($this->carts->isNotEmpty())

        <a href="{{ route('public.checkout') }}" wire:navigate
            class="inline-flex items-center gap-1.5 rounded-sm bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600">
            Checkout
            {{-- heroicons: arrow-right --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>

    @endif

</div>

</div>