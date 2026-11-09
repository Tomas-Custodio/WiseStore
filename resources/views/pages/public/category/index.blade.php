<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    #[Computed]
    public function categories()
    {
        return Category::query()
            ->withCount(['products' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('name')
            ->get();
    }
}; ?>

<article class="">

    <header class="mb-3 flex items-center justify-between gap-3">
        <h1 class="text-xl font-semibold text-gray-800">Categorias</h1>
    </header>

    <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($this->categories as $category)

            <a href="{{ route('public.categories.show', $category) }}" wire:navigate wire:key="category-{{ $category->id }}"
                class="flex flex-col rounded-sm border border-gray-200 bg-white p-4 shadow-sm hover:border-blue-500">

                <h2 class="mb-2 font-medium text-gray-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">

                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 
                        0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 
                        18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />

                </svg> {{ $category->name }}

            </h2>

                <p class="flex-1 text-sm text-gray-500">{{ Str::limit($category->description, 100) }}</p>

                <p class="mt-3 text-xs text-blue-600 bg-red-200 p-1  rounded-sm w-24 ">
                    {{ $category->products_count }} {{ Str::plural('produto', $category->products_count) }}
                </p>
            </a>
        @empty
            <p class="col-span-full rounded-sm border border-gray-200 bg-white p-3 text-center text-sm text-gray-500">
                Nenhuma categoria disponível.
            </p>
        @endforelse
    </section>

</article>
