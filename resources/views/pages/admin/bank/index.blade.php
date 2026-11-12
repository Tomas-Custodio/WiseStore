<?php

use Livewire\Component;
use App\Models\Bank;

new class extends Component {
    public string $search = '';

    public function total()
    {
        return Bank::count();
    }

    public function bancosDiferentes()
    {
        return Bank::distinct('name')->count('name');
    }

    public function delete(Bank $banco)
    {
        $banco->delete();
        session()->flash('msg', 'Conta bancária removida!');
    }

    public function bancos()
    {
        return Bank::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('number', 'like', "%{$this->search}%")
                ->orWhere('iban', 'like', "%{$this->search}%")))
            ->latest()
            ->get();
    }

};
?>

<article class="mx-auto max-w-5xl">

    {{-- Lista dos bancos angolanos (usada para os logótipos na tabela) --}}
    @php
        $banks = [
            ['name' => 'BAI — Banco Angolano de Investimentos', 'domain' => 'bai.ao', 'initials' => 'BAI'],
            ['name' => 'BFA — Banco de Fomento Angola', 'domain' => 'bfa.ao', 'initials' => 'BFA'],
            ['name' => 'BIC — Banco BIC', 'domain' => 'bancobic.ao', 'initials' => 'BIC'],
            ['name' => 'BPC — Banco de Poupança e Crédito', 'domain' => 'bpc.ao', 'initials' => 'BPC'],
            ['name' => 'ATLANTICO — Banco Millennium Atlântico', 'domain' => 'atlantico.ao', 'initials' => 'ATL'],
            ['name' => 'Banco SOL', 'domain' => 'bancosol.ao', 'initials' => 'SOL'],
            ['name' => 'Standard Bank Angola', 'domain' => 'standardbank.co.ao', 'initials' => 'SBA'],
            ['name' => 'Banco Keve', 'domain' => 'bancokeve.ao', 'initials' => 'KEV'],
            ['name' => 'BNI — Banco de Negócios Internacional', 'domain' => 'bni.ao', 'initials' => 'BNI'],
            ['name' => 'Banco Económico', 'domain' => 'bancoeconomico.ao', 'initials' => 'BE'],
            ['name' => 'BCI — Banco de Comércio e Indústria', 'domain' => 'bci.ao', 'initials' => 'BCI'],
        ];
        $bankLogos = collect($banks)->keyBy('name');
    @endphp

    <!-- cabecalho das contas bancarias --->

    <header class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-sm bg-green-50 text-green-600">
                {{-- heroicons: building-library --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                </svg>
            </span>

            <h1 class="text-xl font-semibold text-gray-800">Contas bancárias</h1>
        </div>


        <!---add uma nova conta bancaria ( atalho ) ---->

        <a href="{{ route('bank.create') }}" wire:navigate
            class="flex items-center gap-2 rounded-sm bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
            {{-- heroicons: plus --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova conta bancária
        </a>

    </header>

    <!--- contadores das contas bancarias ---->

    <section class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-2">

        <!--- total de contas ---->

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total de contas</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ $this->total() }}</p>
            </div>
            <span class="flex size-9 items-center justify-center rounded-sm bg-gray-100 text-gray-600">
                {{-- heroicons: credit-card --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                </svg>
            </span>
        </section>

        <!--- bancos diferentes ---->

        <section class="flex items-start justify-between rounded-sm border border-gray-200 bg-white p-4 shadow-sm">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Bancos diferentes</p>
                <p class="mt-2 text-2xl font-semibold text-green-700">{{ $this->bancosDiferentes() }}</p>
            </div>
            <span class="flex size-9 items-center justify-center rounded-sm bg-green-50 text-green-700">
                {{-- heroicons: building-library --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                </svg>
            </span>
        </section>

    </section>

    {{-- Mensagem de sucesso --}}
    @if (session('msg'))
        <div class="mb-3 flex items-center gap-3 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">

            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </div>
    @endif

    {{-- <!--- campo de pesquisa de contas ---> --}}

    <section class="relative mb-3">
        {{-- heroicons: magnifying-glass --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por banco, número ou IBAN..."
            class="w-full rounded-sm border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500" />
    </section>

    {{-- <!--- tabela com todas as contas bancarias da empresa ---> --}}

    <section class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">

        <table class="w-full text-left text-sm">

            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Banco</th>
                    <th class="px-3 py-2 font-medium">Número da conta</th>
                    <th class="px-3 py-2 font-medium">IBAN</th>
                    <th class="px-3 py-2 font-medium">Criada em</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse ($this->bancos() as $banco)
                    @php $bankInfo = $bankLogos->get($banco->name); @endphp

                    <tr wire:key="banco-{{ $banco->id }}" class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-3" x-data="{ broken: false }">

                                {{-- Logótipo em miniatura (ou iniciais se não carregar) --}}
                                <span class="flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-gray-200 bg-white">
                                    @if ($bankInfo)
                                        <img x-show="! broken" src="https://www.google.com/s2/favicons?sz=64&domain={{ $bankInfo['domain'] }}"
                                            alt="{{ $bankInfo['initials'] }}" loading="lazy" class="size-5 object-contain" x-on:error="broken = true">
                                        <span x-show="broken" x-cloak class="text-[9px] font-bold text-gray-600">{{ $bankInfo['initials'] }}</span>
                                    @else
                                        <span class="text-[9px] font-bold text-gray-600">{{ Str::upper(Str::substr($banco->name, 0, 3)) }}</span>
                                    @endif
                                </span>

                                <p class="text-gray-800">{{ $banco->name }}</p>
                            </div>
                        </td>
                        <td class="px-3 py-2 font-mono text-gray-600">{{ $banco->number }}</td>
                        <td class="px-3 py-2 font-mono text-gray-600">{{ $banco->iban }}</td>
                        <td class="px-3 py-2 text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                {{-- heroicons: clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $banco->created_at?->format('d/m/Y') }}
                            </span>
                        </td>

                        {{-- Acções --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <div class="flex items-center justify-end gap-1">

                                <a href="{{ route('bank.edit', $banco) }}" wire:navigate
                                    title="Editar conta" aria-label="Editar conta"
                                    class="inline-flex size-8 items-center justify-center rounded-sm border border-gray-200 text-blue-600 hover:border-blue-200 hover:bg-blue-50">
                                    {{-- heroicons: pencil-square --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </a>

                                <button wire:click="delete({{ $banco->id }})"
                                    wire:confirm="Tens a certeza que queres remover esta conta bancária?"
                                    title="Remover conta" aria-label="Remover conta"
                                    class="inline-flex size-8 cursor-pointer items-center justify-center rounded-sm border border-gray-200 text-red-600 hover:border-red-200 hover:bg-red-50">
                                    {{-- heroicons: trash --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>

                            </div>
                        </td>
                    </tr>

                {{-- <!--- resposta quando nao ha contas ou a pesquisa nao encontra nada ---> --}}

                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                {{-- heroicons: inbox --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                                Nenhuma conta bancária encontrada.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </section>

</article>
