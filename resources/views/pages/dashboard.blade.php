<?php

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component
{
    /**
     * @return array{products: int, activeProducts: int, promotions: int, customers: int, newCustomers: int, comments: int}
     */
    #[Computed]
    public function stats(): array
    {
        return [
            'products' => Product::count(),
            'activeProducts' => Product::where('status', 'active')->count(),
            'promotions' => Product::where('promotion', true)->count(),
            'customers' => User::customers()->count(),
            'newCustomers' => User::customers()->where('created_at', '>=', now()->startOfMonth())->count(),
            'comments' => Comment::count(),
        ];
    }

    /**
     * @return Collection<int, Product>
     */
    #[Computed]
    public function latestProducts(): Collection
    {
        return Product::with('category')->latest()->limit(5)->get();
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function latestCustomers(): Collection
    {
        return User::customers()->latest()->limit(5)->get();
    }
}; ?>

<article class="mx-auto max-w-5xl">

    <header class="mb-4 flex items-center gap-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
            {{-- heroicons: home --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
        </span>
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Dashboard</h1>
            <p class="text-sm text-gray-500">Olá, {{ auth()->user()->name }}. Este é o resumo da loja.</p>
        </div>
    </header>

    <section class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3">

        {{-- Produtos --}}
        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Produtos ativos</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->stats['activeProducts'] }}</p>
                <p class="mt-1 flex flex-wrap items-center gap-1 text-sm text-gray-500">
                    de {{ $this->stats['products'] }} ·
                    {{-- heroicons: tag --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-amber-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    {{ $this->stats['promotions'] }} em promoção
                </p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-gray-600">
                {{-- heroicons: cube --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
            </span>
        </div>

        {{-- Clientes --}}
        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Clientes</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->stats['customers'] }}</p>
                <p class="mt-1 flex items-center gap-1 text-sm text-green-700">
                    {{-- heroicons: arrow-trending-up --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                    </svg>
                    +{{ $this->stats['newCustomers'] }} este mês
                </p>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-green-50 text-green-700">
                {{-- heroicons: users --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </span>
        </div>

        {{-- Comentários --}}
        <div class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Comentários</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->stats['comments'] }}</p>
                <a href="{{ route('comments.index') }}" wire:navigate class="mt-1 inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                    Moderar
                    {{-- heroicons: arrow-right --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </div>
            <span class="flex size-9 shrink-0 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
                {{-- heroicons: chat-bubble-left-right --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            </span>
        </div>

    </section>

    <section class="grid grid-cols-1 gap-3 md:grid-cols-2">

        {{-- Últimos produtos --}}
        <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

            <header class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                    {{-- heroicons: cube --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                    </svg>
                    Últimos produtos
                </h2>
                <a href="{{ route('products.index') }}" wire:navigate
                    class="inline-flex items-center gap-1 rounded-sm px-2 py-1 text-sm text-blue-600 hover:bg-blue-50">
                    Ver todos
                    {{-- heroicons: arrow-right --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </header>

            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2 font-medium">Produto</th>
                        <th class="px-4 py-2 font-medium">Categoria</th>
                        <th class="px-4 py-2 font-medium">Estado</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->latestProducts as $product)
                        <tr wire:key="latest-product-{{ $product->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <span class="flex items-center gap-2 text-gray-800">
                                    {{-- heroicons: cube --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                    </svg>
                                    <span class="truncate">{{ $product->name }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-4 py-2">
                                <span @class([
                                    'rounded-sm px-2 py-1 text-xs font-medium',
                                    'bg-green-50 text-green-700' => $product->status === 'active',
                                    'bg-gray-100 text-gray-600' => $product->status !== 'active',
                                ])>
                                    {{ $product->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('products.edit', $product) }}" wire:navigate
                                    class="inline-flex items-center gap-1 rounded-sm px-2 py-1 text-blue-600 hover:bg-blue-50" title="Editar produto">
                                    {{-- heroicons: pencil-square --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    {{-- heroicons: inbox --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-gray-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                    </svg>
                                    Ainda não há produtos.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        {{-- Novos clientes --}}
        <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

            <header class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <h2 class="flex items-center gap-2 text-sm font-semibold text-gray-800">
                    {{-- heroicons: users --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    Novos clientes
                </h2>
                <a href="{{ route('customers.index') }}" wire:navigate
                    class="inline-flex items-center gap-1 rounded-sm px-2 py-1 text-sm text-blue-600 hover:bg-blue-50">
                    Ver todos
                    {{-- heroicons: arrow-right --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
            </header>

            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-2 font-medium">Cliente</th>
                        <th class="px-4 py-2 font-medium">Registado em</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->latestCustomers as $customer)
                        <tr wire:key="latest-customer-{{ $customer->id }}" class="hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <span class="flex items-center gap-2 text-gray-800">
                                    {{-- heroicons: user-circle --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 shrink-0 text-gray-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <span class="truncate">{{ $customer->name }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-500">
                                <span class="inline-flex items-center gap-1">
                                    {{-- heroicons: calendar --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    {{ $customer->created_at?->format('d/m/Y') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    {{-- heroicons: inbox --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-gray-300">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                    </svg>
                                    Ainda não há clientes.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    </section>

</article>