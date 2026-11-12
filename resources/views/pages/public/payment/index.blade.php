<?php

use App\Models\Bank;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts::public')] class extends Component
{
    use WithFileUploads;

    public Order $order;

    public ?User $user = null;
    public string $total = '';
    public ?int $bancoId = null;

    public $doc;

    public function mount(Order $order) {

        abort_unless($order->user_id === auth()->id(), 403);

        $this->order = $order->load('items.product');
        $this->user = auth()->user();
        $this->total = $order->total;

        // Começa com o primeiro banco guardado na base de dados

        $this->bancoId = Bank::query()->orderBy('id')->value('id');
    }

    public function SendProof(){

        $this->validate([
            'doc' => 'required|file|max:5120',
        ]);

        $docpath = $this->doc->store('PaymentProofs', 'public');

        auth()->user()-> proofpayment()->create([
            'doc' => $docpath,
            'order_id' => $this->order->id,
        ]);

        $this->order->update(['status' => 'review']);
        $this->reset('doc');

        Session()->flash('msg','comprovativo enviado com sucesso! aguarde pela aprovacao!');

    }

    #[Computed]

    public function bancos() {

        return Bank::query()->orderBy('id')->get();
    }

    #[Computed]
    public function bancoEscolhido(): ?Bank {

        return $this->bancos->firstWhere('id', $this->bancoId);
    }
};
?>

<div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 2xl:max-w-7xl">

    {{-- Lista dos bancos angolanos (só para os logótipos) --}}

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

    {{-- Voltar --}}

    <a href="{{ route('public.encomendas.index') }}" wire:navigate

         class="mb-4 inline-flex items-center gap-1.5 py-1 text-sm font-medium text-gray-500 transition hover:text-blue-600">

            {{-- heroicons: arrow-left --}}

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
                        stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" 
                            stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>

        Voltar às minhas encomendas
    </a>

    {{-- Cabeçalho --}}

    <div class="mb-4 flex items-center gap-3">

        <span class="flex size-10 shrink-0 items-center justify-center rounded-sm bg-blue-50
            text-blue-600">

            {{-- heroicons: banknotes --}}

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
            </svg>
        </span>

        <div class="min-w-0">
            <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Pagamento</h1>
            <p class="mt-0.5 truncate text-xs text-gray-500 sm:text-sm">Encomenda Nº{{ $order->id }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>

    </div>

    {{-- Passos do pagamento --}}

    @php
        $currentStep = match ($order->status) {
            'review' => 3,
            'paid' => 4,
            default => $this->bancoEscolhido ? 2 : 1,
        };

        $steps = [
            1 => 'Escolher banco',
            2 => 'Fazer transferência',
            3 => 'Enviar comprovativo',
        ];
    @endphp

    <ol class="mb-2 flex items-center gap-1.5 overflow-hidden rounded-sm border border-gray-200 bg-white p-2.5 shadow-sm sm:mb-6 sm:gap-4 sm:p-4">

        @foreach ($steps as $number => $stepLabel)

            <li class="flex min-w-0 flex-1 items-center gap-1.5 sm:gap-2">

                <span @class([
                    'flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                    'bg-green-600 text-white' => $currentStep > $number,
                    'bg-blue-600 text-white ring-4 ring-blue-100' => $currentStep === $number,
                    'bg-gray-100 text-gray-400' => $currentStep < $number,
                ])>

                    @if ($currentStep > $number)

                        {{-- heroicons: check --}}

                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @else
                        {{ $number }}
                    @endif
                </span>

                <span @class([
                    'hidden text-sm font-medium sm:inline',
                    'text-gray-900' => $currentStep >= $number,
                    'text-gray-400' => $currentStep < $number,
                ])>{{ $stepLabel }}</span>

                @if (! $loop->last)
                    <span @class([
                        'h-0.5 flex-1 rounded-full',
                        'bg-green-500' => $currentStep > $number,
                        'bg-gray-200' => $currentStep <= $number,
                    ])></span>
                @endif
            </li>
        @endforeach
    </ol>

    {{-- No telemóvel os nomes dos passos ficam escondidos, por isso mostramos o passo atual aqui --}}

    <p class="mb-4 text-center text-xs font-medium text-gray-500 sm:hidden">
        @if ($currentStep > count($steps))
            Pagamento concluído
        @else
            Passo {{ $currentStep }} de {{ count($steps) }} · <span class="text-gray-900">{{ $steps[$currentStep] }}</span>
        @endif
    </p>

    <div class="grid gap-4 lg:grid-cols-5 xl:gap-6">

        {{-- ======================= Lado esquerdo: encomenda e produtos ======================= --}}

        <section class="min-w-0 self-start overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm lg:col-span-3">

            <header class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100
                 bg-gray-50 px-4 py-3">

                <div class="flex items-center gap-2">

                    {{-- heroicons: shopping-bag --}}

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
                        stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-400">

                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>

                    <p class="text-sm font-semibold text-gray-900">
                            Produtos da encomenda
                    </p>

                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium text-gray-600">
                        {{ $order->items->count() }}
                    </span>

                </div>

                <span @class([
                    'inline-flex shrink-0 items-center gap-1.5 rounded-sm px-2 py-1 text-xs font-semibold',
                    'bg-yellow-100 text-yellow-700' => $order->status === 'pending',
                    'bg-blue-100 text-blue-700' => $order->status === 'review',
                    'bg-green-100 text-green-700' => $order->status === 'paid',
                    'bg-red-100 text-red-700' => $order->status === 'rejected',
                ])>

                    <span class="size-1.5 rounded-full bg-current"></span>

                    {{ match ($order->status) {
                        'pending' => 'Pendente',
                        'review' => 'Em análise',
                        'paid' => 'Paga',
                        'rejected' => 'Rejeitada',
                        default => ucfirst($order->status),
                    } }}
                </span>
            </header>

            {{-- Telemóvel e tablet pequeno: lista de cartões --}}

            <ul class="divide-y divide-gray-100 md:hidden">

                @foreach ($order->items as $item)

                    <li wire:key="item-mobile-{{ $item->id }}" class="flex items-center gap-3 px-3 py-3 sm:px-4">

                        @if ($item->product?->img)
                            <img src="{{ asset('storage/'.$item->product->img) }}" alt="{{ $item->product_name }}"
                                class="size-12 shrink-0 rounded-sm object-cover ring-1 ring-gray-200 sm:size-14">
                        @else
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-xl sm:size-14">📦</div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="line-clamp-2 break-words text-sm font-medium text-gray-900">{{ $item->product_name }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                {{ $item->quantity }} × {{ number_format($item->preco, 0, ',', ' ') }} Kz
                            </p>
                        </div>

                        <p class="whitespace-nowrap text-sm font-semibold text-gray-900">
                            {{ number_format($item->preco * $item->quantity, 0, ',', ' ') }} Kz
                        </p>
                    </li>
                @endforeach
            </ul>

            {{-- Tablet grande e desktop: tabela --}}

            <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left">
                <thead class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Produto</th>
                        <th class="px-4 py-2 text-center">Qtd.</th>
                        <th class="px-4 py-2 text-right">Preço</th>
                        <th class="px-4 py-2 text-right">Subtotal</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                    @foreach ($order->items as $item)

                        <tr wire:key="item-{{ $item->id }}" class="transition hover:bg-gray-50">
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-3">

                                    @if ($item->product?->img)
                                        <img src="{{ asset('storage/'.$item->product->img) }}" alt="{{ $item->product_name }}"
                                            class="size-12 shrink-0 rounded-sm object-cover ring-1 ring-gray-200">
                                    @else
                                        <div class="flex size-12 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-lg">📦</div>
                                    @endif

                                    <span class="line-clamp-2 text-sm font-medium text-gray-900">{{ $item->product_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center text-sm text-gray-600">{{ $item->quantity }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm text-gray-600">{{ number_format($item->preco, 0, ',', ' ') }} Kz</td>
                            <td class="whitespace-nowrap px-4 py-2 text-right text-sm font-semibold text-gray-900">{{ number_format($item->preco * $item->quantity, 0, ',', ' ') }} Kz</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <footer class="flex items-center justify-between gap-2 border-t border-gray-100 bg-gray-50 px-3 py-3 sm:px-4">
                <span class="text-sm font-medium text-gray-600">Total a pagar</span>
                <span class="whitespace-nowrap text-base font-bold text-gray-900 sm:text-lg md:text-xl">{{ number_format($this->total, 0, ',', ' ') }} Kz</span>
            </footer>
        </section>

        {{-- ======================= Lado direito: escolher o banco ======================= --}}

        <section class="min-w-0 space-y-4 self-start lg:sticky lg:top-4 lg:col-span-2 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">

            <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

                <header class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3">

                    {{-- heroicons: building-library --}}

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 
                            21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>

                    <p class="text-sm font-semibold text-gray-900">Escolha o banco para a transferência</p>
                </header>

                <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-1">

                    @forelse ($this->bancos as $banco)
                        @php $bankInfo = $bankLogos->get($banco->name); @endphp

                        <label wire:key="banco-{{ $banco->id }}"
                            @class([
                                'flex cursor-pointer items-center gap-3 rounded-sm border px-3 py-3 transition sm:py-2.5',
                                'border-blue-500 bg-blue-50 ring-1 ring-blue-500' => $bancoId === $banco->id,
                                'border-gray-200 hover:border-gray-300 hover:bg-gray-50' => $bancoId !== $banco->id,
                            ])>

                            <input type="radio" wire:model.live="bancoId" value="{{ $banco->id }}" class="sr-only">

                            {{-- Logótipo em miniatura (ou iniciais se não carregar) --}}

                            <span class="flex size-8 shrink-0 items-center justify-center overflow-hidden 
                                    rounded-sm border border-gray-200 bg-white" x-data="{ broken: false }">

                                @if ($bankInfo)
                                    <img x-show="! broken" src="https://www.google.com/s2/favicons?sz=64&domain={{ $bankInfo['domain'] }}"
                                        alt="{{ $bankInfo['initials'] }}" class="size-5 object-contain" x-on:error="broken = true">
                                    <span x-show="broken" x-cloak class="text-[9px] font-bold text-gray-600">{{ $bankInfo['initials'] }}</span>
                                @else
                                    <span class="text-[9px] font-bold text-gray-600">{{ Str::upper(Str::substr($banco->name, 0, 3)) }}</span>
                                @endif
                            </span>

                            <span class="min-w-0 flex-1 line-clamp-2 break-words text-sm font-medium leading-tight text-gray-800">{{ $banco->name }}</span>

                            {{-- heroicons: check-circle (solid) --}}

                            @if ($bancoId === $banco->id)
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0 text-blue-600">
                                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <span class="size-5 shrink-0 rounded-full border-2 border-gray-300"></span>
                            @endif

                        </label>

                    @empty

                        <div class="col-span-full flex flex-col items-center gap-2 py-6 text-center text-sm text-gray-500">
                            {{-- heroicons: inbox --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                                stroke="currentColor" class="size-10 text-gray-300">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                            </svg>
                            Ainda não há contas bancárias disponíveis.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Dados da conta escolhida --}}

            @if ($this->bancoEscolhido)
                <div class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

                    <header class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3">
                        {{-- heroicons: credit-card --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                        <p class="text-sm font-semibold text-gray-900">Dados para transferência</p>
                    </header>

                    <dl class="divide-y divide-gray-100 text-sm">
                        @foreach ([
                            'Banco' => $this->bancoEscolhido->name,
                            'Número da conta' => $this->bancoEscolhido->number,
                            'IBAN' => $this->bancoEscolhido->iban,
                            'Referência' => 'Encomenda Nº'.$order->id,
                        ] as $label => $value)
                            <div class="flex items-center justify-between gap-3 px-3 py-2.5 sm:px-4" x-data="{ copied: false }">
                                <div class="min-w-0 flex-1">
                                    <dt class="text-xs text-gray-500">{{ $label }}</dt>
                                    <dd class="break-all font-mono text-xs font-medium text-gray-900 sm:text-sm">{{ $value }}</dd>
                                </div>

                                <button type="button" title="Copiar" aria-label="Copiar {{ $label }}"
                                    x-on:click="navigator.clipboard.writeText(@js((string) $value)); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="inline-flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-sm border border-gray-200 text-gray-500 transition hover:bg-gray-50 hover:text-blue-600">
                                    {{-- heroicons: document-duplicate --}}
                                    <svg x-show="! copied" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                                    </svg>
                                    {{-- heroicons: check --}}
                                    <svg x-show="copied" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4 text-green-600">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    </dl>

                    <footer class="flex items-center justify-between gap-2 border-t border-gray-100 bg-blue-50 px-4 py-3">
                        <span class="text-sm font-medium text-blue-700">Valor a transferir</span>
                        <span class="whitespace-nowrap text-base font-bold text-blue-700 sm:text-lg">{{ number_format($this->total, 0, ',', ' ') }} Kz</span>
                    </footer>
                </div>

                <p class="flex items-start gap-2 text-xs text-gray-500">
                    {{-- heroicons: information-circle --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    Use a referência «Encomenda Nº{{ $order->id }}» na descrição da transferência.
                </p>
            @endif

            {{-- Mensagem do sistema --}}
            @if (session('msg'))
                <p class="flex items-center gap-2 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                    {{-- heroicons: check-circle --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ session('msg') }}
                </p>
            @endif

            {{-- ======================= Comprovativo de pagamento ======================= --}}

            @if ($order->status === 'review')

                {{-- Enviado, à espera do admin --}}
                <div class="flex items-start gap-3 rounded-sm border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                    {{-- heroicons: clock --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Comprovativo em análise</p>
                        <p class="mt-0.5 text-blue-700">Já recebemos o seu comprovativo. Vamos confirmar o pagamento em breve.</p>
                    </div>
                </div>

            @elseif ($order->status === 'paid')

                {{-- Aceite pelo admin --}}
                <div class="flex items-start gap-3 rounded-sm border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    {{-- heroicons: check-badge --}}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                    </svg>
                    <div>
                        <p class="font-semibold">Pagamento confirmado</p>
                        <p class="mt-0.5 text-green-700">Obrigado! A sua encomenda está paga.</p>
                    </div>
                </div>

            @elseif ($this->bancoEscolhido)

                {{-- Enviar (ou reenviar) o comprovativo --}}
                <form wire:submit="SendProof" class="overflow-hidden rounded-sm border border-gray-200 bg-white shadow-sm">

                    <header class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-4 py-3">
                        {{-- heroicons: document-arrow-up --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-gray-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12-3-3m0 0-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                        <p class="text-sm font-semibold text-gray-900">Enviar comprovativo</p>
                    </header>

                    <div class="space-y-3 p-4">

                        @if ($order->status === 'rejected')
                            <p class="flex items-start gap-2 rounded-sm border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                                {{-- heroicons: x-circle --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span>
                                    O comprovativo anterior foi rejeitado. Verifique a transferência e envie um novo.
                                    @if ($order->paymentproof?->msg)
                                        <span class="mt-1 block"><strong>Motivo:</strong> {{ $order->paymentproof->msg }}</span>
                                    @endif
                                </span>
                            </p>
                        @endif

                        {{-- Zona para escolher o ficheiro --}}
                        <label for="doc" @class([
                            'flex cursor-pointer flex-col items-center gap-2 rounded-sm border-2 border-dashed px-3 py-5 text-center transition hover:bg-gray-50 sm:px-4 sm:py-6',
                            'border-red-300' => $errors->has('doc'),
                            'border-gray-300' => ! $errors->has('doc'),
                        ])>
                            {{-- heroicons: cloud-arrow-up --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8 text-gray-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>

                            @if ($doc)
                                <span class="max-w-full break-all text-sm font-medium text-gray-900">{{ $doc->getClientOriginalName() }}</span>
                                <span class="text-xs text-gray-500">Clique para trocar de ficheiro</span>
                            @else
                                <span class="text-sm font-medium text-gray-700">Clique para escolher o comprovativo</span>
                                <span class="text-xs text-gray-500">PDF, JPG ou PNG · até 5 MB</span>
                            @endif

                            <span wire:loading wire:target="doc" class="text-xs text-blue-600">A carregar o ficheiro...</span>
                        </label>

                        <input type="file" id="doc" wire:model="doc" accept=".pdf,.jpg,.jpeg,.png" class="sr-only">

                        @error('doc')
                            <p class="flex items-center gap-1 text-xs text-red-600">
                                {{-- heroicons: exclamation-circle --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror

                        <button type="submit" wire:loading.attr="disabled" wire:target="SendProof, doc"

                            class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-sm
                                 bg-green-600 px-4 py-3 text-sm font-semibold text-white shadow-sm 
                                    transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50 sm:py-2.5">

                            {{-- heroicons: paper-airplane --}}

                            <svg wire:loading.remove wire:target="SendProof" xmlns="http://www.w3.org/2000/svg" 
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 
                                    0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>

                            {{-- heroicons: arrow-path (a girar durante o envio) --}}

                            <svg wire:loading wire:target="SendProof" xmlns="http://www.w3.org/2000/svg" fill="none" 
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 animate-spin">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 
                                    19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 
                                9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>

                            Enviar comprovativo

                        </button>

                    </div>
                </form>
            @endif
        </section>

    </div>

</div>