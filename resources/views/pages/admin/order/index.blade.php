<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $search = '';

    /**
     * Filtro por estado: '' (todas), pending, review, paid ou rejected.
     */
    public string $status = 'review';

    /**
     * Encomenda cujo comprovativo está a ser rejeitado (abre a janela do motivo).
     */
    public ?int $rejectingOrderId = null;

    public string $rejectionReason = '';

    public string $rejectionNote = '';

    public function contar(string $status): int
    {
        return Order::where('status', $status)->count();
    }

    public function orders()
    {
        return Order::query()
            ->with(['user', 'items', 'paymentproofs' => fn ($q) => $q->latest()])
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q
                ->where('id', $this->search)
                ->orWhereHas('user', fn ($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))))
            ->latest()
            ->get();
    }

    /**
     * Abre/descarrega um comprovativo enviado pelo cliente (tabela payment_proofs).
     */
    public function download(PaymentProof $proof)
    {
        abort_unless(Storage::disk('public')->exists($proof->doc), 404);

        return Storage::disk('public')->download($proof->doc, "comprovativo-encomenda-{$proof->order_id}-{$proof->id}.".pathinfo($proof->doc, PATHINFO_EXTENSION));
    }

    /**
     * Admin aceita o comprovativo mais recente: fica "verified" e o pagamento é gravado em "payments".
     */
    public function accept(Order $order)
    {
        $proof = $order->paymentproof;

        abort_unless($order->status === 'review' && $proof?->status === 'pending', 403);

        DB::transaction(function () use ($order, $proof) {
            $proof->update(['status' => 'verified']);

            Payment::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'doc' => $proof->doc,
            ]);

            $order->update(['status' => 'paid']);
        });

        session()->flash('msg', "Pagamento da encomenda Nº{$order->id} aceite!");
    }

    /**
     * Abre a janela para escolher o motivo da rejeição.
     */
    public function openReject(Order $order)
    {
        abort_unless($order->status === 'review', 403);

        $this->resetValidation();
        $this->reset('rejectionReason', 'rejectionNote');
        $this->rejectingOrderId = $order->id;
    }

    public function cancelReject()
    {
        $this->reset('rejectingOrderId', 'rejectionReason', 'rejectionNote');
    }

    /**
     * Admin rejeita o comprovativo com um motivo: fica no histórico e o cliente pode reenviar outro.
     */
    public function reject()
    {
        $this->validate([
            'rejectionReason' => ['required', Rule::in(array_keys(PaymentProof::REJECTION_REASONS))],
            'rejectionNote' => ['nullable', 'string', 'max:500', Rule::requiredIf($this->rejectionReason === 'outro')],
        ], [
            'rejectionReason.required' => 'Escolha o motivo da rejeição.',
            'rejectionNote.required' => 'Descreva o motivo.',
        ]);

        $order = Order::findOrFail($this->rejectingOrderId);
        $proof = $order->paymentproof;

        abort_unless($order->status === 'review' && $proof?->status === 'pending', 403);

        DB::transaction(function () use ($order, $proof) {
            $proof->update([
                'status' => 'rejected',
                'rejection_reason' => $this->rejectionReason,
                'rejection_note' => $this->rejectionNote ?: null,
            ]);

            $order->update(['status' => 'rejected']);
        });

        session()->flash('msg', "Comprovativo da encomenda Nº{$order->id} rejeitado.");

        $this->cancelReject();
    }
};
?>

<article class="mx-auto max-w-6xl">

    <!-- cabecalho das encomendas --->

    <header class="mb-3 flex items-center gap-3">
        <span class="flex size-10 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
            {{-- heroicons: shopping-bag --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            </svg>
        </span>

        <h1 class="text-xl font-semibold text-gray-800">Encomendas</h1>
    </header>

    <!--- contadores por estado (clicar filtra a tabela) ---->

    @php
        $cards = [
            'review' => ['label' => 'Em análise', 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'ring' => 'ring-blue-500',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'pending' => ['label' => 'Por pagar', 'color' => 'text-yellow-700', 'bg' => 'bg-yellow-50', 'ring' => 'ring-yellow-500',
                'icon' => 'M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3'],
            'paid' => ['label' => 'Pagas', 'color' => 'text-green-700', 'bg' => 'bg-green-50', 'ring' => 'ring-green-500',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'rejected' => ['label' => 'Rejeitadas', 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'ring' => 'ring-red-500',
                'icon' => 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
    @endphp

    <section class="mb-3 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($cards as $key => $card)
            <button type="button" wire:click="$set('status', '{{ $status === $key ? '' : $key }}')"
                @class([
                    'flex cursor-pointer items-start justify-between rounded-sm border border-gray-200 bg-white p-4 text-left shadow-sm transition hover:border-gray-300',
                    'ring-2 '.$card['ring'] => $status === $key,
                ])>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-semibold {{ $card['color'] }}">{{ $this->contar($key) }}</p>
                </div>
                <span class="flex size-9 items-center justify-center rounded-sm {{ $card['bg'] }} {{ $card['color'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" />
                    </svg>
                </span>
            </button>
        @endforeach
    </section>

    {{-- <!--- Msg do sistema ---> --}}

    @if (session('msg'))
        <p class="mb-3 flex items-center gap-2 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">
            {{-- heroicons: check-circle --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('msg') }}
        </p>
    @endif

    {{-- <!--- pesquisa + filtro ---> --}}

    <section class="mb-3 flex gap-3">
        <div class="relative flex-1">
            {{-- heroicons: magnifying-glass --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por Nº da encomenda, nome ou email do cliente..."
                class="w-full rounded-sm border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
        </div>

        <select wire:model.live="status"
            class="rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            <option value="">Todos os estados</option>
            @foreach (App\Models\Order::STATUS_LABELS as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </section>

    {{-- <!--- tabela das encomendas ---> --}}

    <section class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">

        <table class="w-full text-left text-sm">

            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Encomenda</th>
                    <th class="px-3 py-2 font-medium">Cliente</th>
                    <th class="px-3 py-2 font-medium">Total</th>
                    <th class="px-3 py-2 font-medium">Estado</th>
                    <th class="px-3 py-2 font-medium">Comprovativo</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

                @forelse ($this->orders() as $order)
                    <tr wire:key="order-{{ $order->id }}" class="hover:bg-gray-50">

                        {{-- Encomenda --}}
                        <td class="px-3 py-2">
                            <p class="font-medium text-gray-800">Nº{{ $order->id }}</p>
                            <p class="inline-flex items-center gap-1 text-xs text-gray-500">
                                {{-- heroicons: clock --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                {{ $order->created_at?->format('d/m/Y H:i') }} · {{ $order->items->sum('quantity') }} artigo(s)
                            </p>
                        </td>

                        {{-- Cliente --}}
                        <td class="px-3 py-2">
                            <div class="flex items-center gap-2">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-sm bg-gray-100 text-xs font-semibold text-gray-600">
                                    {{ Str::upper(Str::substr($order->user?->name ?? '?', 0, 2)) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-gray-800">{{ $order->user?->name }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ $order->user?->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Total --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <span class="inline-flex items-center gap-1 font-semibold text-gray-800">
                                {{-- heroicons: banknotes --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 text-green-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                                {{ number_format($order->total, 0, ',', ' ') }} Kz
                            </span>
                        </td>

                        {{-- Estado --}}
                        <td class="px-3 py-2">
                            <span @class([
                                'rounded-sm px-2 py-1 text-xs font-semibold',
                                'bg-yellow-100 text-yellow-700' => $order->status === 'pending',
                                'bg-blue-100 text-blue-700' => $order->status === 'review',
                                'bg-green-100 text-green-700' => $order->status === 'paid',
                                'bg-red-100 text-red-700' => $order->status === 'rejected',
                            ])>
                                {{ App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                            </span>
                        </td>

                        {{-- Comprovativo --}}
                        <td class="px-3 py-2">
                            <ul class="space-y-1.5">
                                @forelse ($order->paymentproofs as $proof)
                                    <li wire:key="proof-{{ $proof->id }}" class="min-w-52 rounded-sm border border-gray-200 p-1.5">
                                        <div class="flex items-center justify-between gap-2">

                                            <div class="min-w-0">
                                                <span @class([
                                                    'rounded-sm px-1.5 py-0.5 text-[11px] font-semibold',
                                                    'bg-yellow-100 text-yellow-700' => $proof->status === 'pending',
                                                    'bg-green-100 text-green-700' => $proof->status === 'verified',
                                                    'bg-red-100 text-red-700' => $proof->status === 'rejected',
                                                ])>
                                                    {{ App\Models\PaymentProof::STATUS_LABELS[$proof->status] ?? $proof->status }}
                                                </span>
                                                <p class="mt-1 text-[11px] text-gray-500">{{ $proof->created_at->format('d/m/Y H:i') }}</p>
                                            </div>

                                            <div class="flex shrink-0 items-center gap-1">
                                                {{-- Abrir numa nova aba para ler --}}
                                                <a href="{{ route('payment-proofs.show', $proof) }}" target="_blank" rel="noopener"
                                                    title="Abrir numa nova aba" aria-label="Abrir comprovativo numa nova aba"
                                                    class="flex size-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600">
                                                    {{-- heroicons: arrow-top-right-on-square --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                    </svg>
                                                </a>

                                                {{-- Descarregar (estilo iPhone) --}}
                                                <button type="button" wire:click="download({{ $proof->id }})"
                                                    title="Descarregar" aria-label="Descarregar comprovativo"
                                                    class="flex size-8 cursor-pointer items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600">
                                                    {{-- heroicons: arrow-down-tray --}}
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        @if ($proof->status === 'rejected')
                                            <p class="mt-1.5 flex items-start gap-1 rounded-sm bg-red-50 p-1.5 text-[11px] text-red-700">
                                                {{-- heroicons: information-circle --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3.5 shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                                </svg>
                                                <span>
                                                    <strong>{{ $proof->rejectionReasonLabel() }}</strong>
                                                    @if ($proof->rejection_note)
                                                        — {{ $proof->rejection_note }}
                                                    @endif
                                                </span>
                                            </p>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-xs text-gray-400">—</li>
                                @endforelse
                            </ul>
                        </td>

                        {{-- Acções: aceitar / rejeitar --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <div class="flex items-center justify-end gap-1">
                                @if ($order->status === 'review')
                                    <button wire:click="accept({{ $order->id }})"
                                        wire:confirm="Aceitar o pagamento da encomenda Nº{{ $order->id }}?"
                                        title="Aceitar pagamento" aria-label="Aceitar pagamento"
                                        class="inline-flex cursor-pointer items-center gap-1 rounded-sm bg-green-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                        {{-- heroicons: check --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                        Aceitar
                                    </button>

                                    <button wire:click="openReject({{ $order->id }})"
                                        title="Rejeitar comprovativo" aria-label="Rejeitar comprovativo"
                                        class="inline-flex cursor-pointer items-center gap-1 rounded-sm border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:border-red-200 hover:bg-red-50">
                                        {{-- heroicons: x-mark --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                        Rejeitar
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">Sem acções</span>
                                @endif
                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                {{-- heroicons: inbox --}}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 text-gray-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.35 13.177a2.25 2.25 0 0 0-.1.661Z" />
                                </svg>
                                Nenhuma encomenda encontrada.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </section>

    {{-- <!--- janela: motivo da rejeição ---> --}}

    @if ($rejectingOrderId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" wire:keydown.escape.window="cancelReject">
            <form wire:submit="reject" class="w-full max-w-md overflow-hidden rounded-sm bg-white shadow-xl">

                <header class="flex items-center gap-3 border-b border-gray-100 px-4 py-3">
                    <span class="flex size-9 items-center justify-center rounded-sm bg-red-50 text-red-600">
                        {{-- heroicons: shield-exclamation --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-semibold text-gray-900">Rejeitar comprovativo</p>
                        <p class="text-xs text-gray-500">Encomenda Nº{{ $rejectingOrderId }} · o cliente terá de enviar outro.</p>
                    </div>
                </header>

                <div class="space-y-3 p-4">
                    <div>
                        <label for="rejectionReason" class="mb-1 block text-sm font-medium text-gray-700">Motivo</label>
                        <select id="rejectionReason" wire:model.live="rejectionReason"
                            class="w-full rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                            <option value="">Escolha o motivo...</option>
                            @foreach (App\Models\PaymentProof::REJECTION_REASONS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('rejectionReason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="rejectionNote" class="mb-1 block text-sm font-medium text-gray-700">
                            Informação adicional
                            @if ($rejectionReason !== 'outro') <span class="font-normal text-gray-400">(opcional)</span> @endif
                        </label>
                        <textarea id="rejectionNote" wire:model="rejectionNote" rows="3" maxlength="500"
                            placeholder="Ex.: o número da transacção não existe no extracto bancário."
                            class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                        @error('rejectionNote') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <footer class="flex justify-end gap-2 border-t border-gray-100 bg-gray-50 px-4 py-3">
                    <button type="button" wire:click="cancelReject"
                        class="cursor-pointer rounded-sm border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="cursor-pointer rounded-sm bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700">
                        <span wire:loading.remove wire:target="reject">Rejeitar</span>
                        <span wire:loading wire:target="reject">A rejeitar...</span>
                    </button>
                </footer>
            </form>
        </div>
    @endif

</article>
