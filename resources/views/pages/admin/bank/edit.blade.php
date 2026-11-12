<?php

use Livewire\Component;
use App\Models\Bank;

new class extends Component {

    public Bank $banco;

    public string $name = '';
    public string $number = '';
    public string $iban = '';

    public function mount(Bank $banco) {

        $this->banco = $banco;
        $this->name = $banco->name;
        $this->number = (string) $banco->number;
        $this->iban = (string) $banco->iban;
    }

    public function save() {

        $data = $this->validate([
            'name'=> 'required|string|max:50',
            'number'=>'required|string|max:25',
            'iban'=> ['required', 'string', 'regex:/^AO\d{23}$/'],
        ], [
            'iban.regex' => 'O IBAN deve começar por AO seguido de 23 dígitos (ex.: AO06004000001234567890123).',
        ]);

        $this->banco->update($data);
        session()->flash('msg', 'Conta bancária atualizada!');
    }
};
?>

<article class="mx-auto max-w-3xl px-4 py-10">

    {{-- Lista dos bancos angolanos (usada no select) --}}
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
    @endphp

    {{-- Voltar --}}
    <a href="{{ route('bank.index') }}" wire:navigate
        class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-green-600">

        {{-- heroicons: arrow-left --}}
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Voltar às contas bancárias
    </a>

    {{-- Mensagem de sucesso --}}
    @if (session('msg'))
        <div class="mb-6 flex items-center gap-3 rounded-sm border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">

            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </div>
    @endif

    {{-- Card do formulário --}}
    <form wire:submit="save" class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

        {{-- Cabeçalho do card --}}
        <header class="flex items-center gap-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-white px-6 py-5">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-sm bg-green-600 text-white shadow-sm">

                {{-- heroicons: building-library --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                </svg>
            </div>

            <div>
                <h1 class="text-xl font-bold text-gray-900">Editar conta bancária</h1>
                <p class="mt-0.5 text-sm text-gray-500">Altere os dados desta conta bancária.</p>
            </div>
        </header>

        {{-- Campos --}}
        <div class="space-y-6 px-6 py-6">

            {{-- Banco (select com logótipos) --}}
            <section class="space-y-1.5"
                x-data="{
                    open: false,
                    banks: @js($banks),
                    broken: {},
                    get selectedBank() { return this.banks.find(bank => bank.name === $wire.name) },
                    logo(bank) { return 'https://www.google.com/s2/favicons?sz=64&domain=' + bank.domain },
                    choose(bank) { $wire.name = bank.name; this.open = false },
                }"
                @click.outside="open = false"
                @keydown.escape="open = false">

                <label for="name" class="block text-sm font-semibold text-gray-700">
                    Banco <span class="text-red-500">*</span>
                </label>

                <div class="relative">

                    {{-- Botão que abre a lista --}}
                    <button type="button" id="name" @click="open = ! open"
                        :aria-expanded="open" aria-haspopup="listbox"
                        @class([
                            'flex w-full cursor-pointer items-center gap-3 rounded-sm border bg-white py-2.5 pl-3 pr-10 text-left text-sm transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('name'),
                            'border-gray-300 focus:border-green-500 focus:ring-green-100' => ! $errors->has('name'),
                        ])>

                        {{-- Banco escolhido --}}
                        <template x-if="selectedBank">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="flex size-6 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-gray-200 bg-white">
                                    <img x-show="! broken[selectedBank.domain]" :src="logo(selectedBank)" :alt="selectedBank.initials"
                                        class="size-5 object-contain" x-on:error="broken[selectedBank.domain] = true">
                                    <span x-show="broken[selectedBank.domain]" x-text="selectedBank.initials"
                                        class="text-[8px] font-bold text-gray-600"></span>
                                </span>
                                <span class="truncate text-gray-900" x-text="selectedBank.name"></span>
                            </span>
                        </template>

                        {{-- Nenhum banco escolhido --}}
                        <template x-if="! selectedBank">
                            <span class="flex items-center gap-3 text-gray-400">

                                {{-- heroicons: building-library --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                </svg>
                                Escolha o seu banco
                            </span>
                        </template>

                        {{-- heroicons: chevron-up-down --}}
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                            </svg>
                        </span>
                    </button>

                    {{-- Lista de bancos --}}
                    <ul x-show="open" x-cloak x-transition.opacity.duration.150ms role="listbox"
                        class="absolute z-20 mt-1 max-h-72 w-full overflow-auto rounded-sm border border-gray-200 bg-white py-1 shadow-lg">

                        <template x-for="bank in banks" :key="bank.domain">
                            <li role="option" :aria-selected="selectedBank?.name === bank.name"
                                @click="choose(bank)"
                                :class="selectedBank?.name === bank.name ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50'"
                                class="flex cursor-pointer items-center gap-3 px-3 py-2 text-sm transition">

                                {{-- Logótipo em miniatura (ou iniciais se não carregar) --}}
                                <span class="flex size-7 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-gray-200 bg-white">
                                    <img x-show="! broken[bank.domain]" :src="logo(bank)" :alt="bank.initials" loading="lazy"
                                        class="size-5 object-contain" x-on:error="broken[bank.domain] = true">
                                    <span x-show="broken[bank.domain]" x-text="bank.initials"
                                        class="text-[9px] font-bold text-gray-600"></span>
                                </span>

                                <span class="flex-1 truncate" x-text="bank.name"></span>

                                {{-- heroicons: check --}}
                                <svg x-show="selectedBank?.name === bank.name" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </li>
                        </template>
                    </ul>
                </div>

                @error('name')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- Número da conta --}}
            <section class="space-y-1.5">
                <label for="number" class="block text-sm font-semibold text-gray-700">
                    Número da conta <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                        {{-- heroicons: hashtag --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5-3.9 19.5m-2.1-19.5-3.9 19.5" />
                        </svg>
                    </div>

                    <input type="text" inputmode="numeric" wire:model="number" id="number" maxlength="25" placeholder="Ex.: 123456789"
                        @class([
                            'w-full rounded-sm border py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('number'),
                            'border-gray-300 focus:border-green-500 focus:ring-green-100' => ! $errors->has('number'),
                        ]) />
                </div>

                @error('number')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

            {{-- IBAN --}}
            <section class="space-y-1.5">
                <label for="iban" class="block text-sm font-semibold text-gray-700">
                    IBAN <span class="text-red-500">*</span>
                </label>

                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">

                        {{-- heroicons: credit-card --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                    </div>

                    <input type="text" wire:model="iban" id="iban" maxlength="25" placeholder="Ex.: AO06004000001234567890123"
                        @class([
                            'w-full rounded-sm border py-2.5 pl-10 pr-3 text-sm text-gray-900 placeholder:text-gray-400 transition focus:outline-none focus:ring-2',
                            'border-red-300 focus:border-red-500 focus:ring-red-100' => $errors->has('iban'),
                            'border-gray-300 focus:border-green-500 focus:ring-green-100' => ! $errors->has('iban'),
                        ]) />
                </div>

                @error('iban')
                    <p class="flex items-center gap-1 text-xs text-red-600">

                        {{-- heroicons: exclamation-circle --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </section>

        </div>

        {{-- Rodapé com ações --}}
        <footer class="flex justify-end border-t border-gray-100 bg-gray-50 px-6 py-4">

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-sm bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">

                {{-- heroicons: check (escondido durante o envio) --}}
                <svg wire:loading.remove wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>

                {{-- heroicons: arrow-path (a girar durante o envio) --}}
                <svg wire:loading wire:target="save" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 animate-spin">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>

                Guardar alterações
            </button>
        </footer>
    </form>

</article>
