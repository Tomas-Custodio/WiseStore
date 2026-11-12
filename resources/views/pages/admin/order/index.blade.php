<?php

use App\Models\PaymentProof;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component
{
    public string $term = '';
    public string $status = '';

    /**
     * Comprovativo que está a ser rejeitado (abre a janela do motivo).
     */
    public ?int $rejectingId = null;

    /**
     * Motivo da rejeição escrito pelo admin.
     */
    public string $msg = '';

    /**
     * Comprovativos, filtrados pela pesquisa (estado / mensagem) e pelo estado.
     */
    public function proofs()
    {
        return PaymentProof::with('order.user')
            ->when(trim($this->term), fn ($q, $term) => $q->where(fn ($q) => $q
                ->where('status', 'like', "%{$term}%")
                ->orWhere('msg', 'like', "%{$term}%")))
            ->when($this->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->get();
    }

    public function contar(string $status): int
    {
        return PaymentProof::where('status', $status)->count();
    }

    public function accept(PaymentProof $proof): void
    {
        abort_unless($proof->status === 'pending', 403);

        DB::transaction(function () use ($proof) {
            $proof->update(['status' => 'accepted']);
            $proof->order->update(['status' => 'paid']);
        });

        session()->flash('msg', 'Pagamento aceite!');
    }

    /**
     * Abre a janela para o admin escrever o motivo da rejeição.
     */
    public function openReject(PaymentProof $proof): void
    {
        abort_unless($proof->status === 'pending', 403);

        $this->resetValidation();
        $this->reset('msg');
        $this->rejectingId = $proof->id;
    }

    public function cancelReject(): void
    {
        $this->resetValidation();
        $this->reset('rejectingId', 'msg');
    }

    /**
     * Rejeita o comprovativo com o motivo obrigatório escrito pelo admin.
     */
    public function reject(): void
    {
        $this->validate([
            'msg' => ['required', 'string', 'max:255'],
        ], [
            'msg.required' => 'Escreva o motivo da rejeição.',
            'msg.max' => 'O motivo não pode ter mais de 255 caracteres.',
        ]);

        $proof = PaymentProof::findOrFail($this->rejectingId);

        abort_unless($proof->status === 'pending', 403);

        DB::transaction(function () use ($proof) {
            $proof->update(['status' => 'rejected', 'msg' => trim($this->msg)]);
            $proof->order->update(['status' => 'rejected']);
        });

        session()->flash('msg', "Comprovativo da encomenda Nº{$proof->order_id} rejeitado.");

        $this->cancelReject();
    }

    public function download(PaymentProof $proof)
    {
        abort_unless(Storage::disk('public')->exists($proof->doc), 404);

        return Storage::disk('public')->download($proof->doc);
    }
};
?>

<article class="mx-auto max-w-6xl">

    <header class="mb-3 flex items-center gap-3">
        <span class="flex size-10 items-center justify-center rounded-sm bg-blue-50 text-blue-600">
            {{-- heroicons: document-check --}}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12" />
            </svg>
        </span>

        <h1 class="text-xl font-semibold text-gray-800">Comprovativos de pagamento</h1>
    </header>

    {{-- contadores por estado (clicar filtra a tabela) --}}

    @php
        $cards = [
            'pending' => ['label' => 'À espera da aprovação', 'color' => 'text-yellow-700', 'bg' => 'bg-yellow-50', 'ring' => 'ring-yellow-500',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'accepted' => ['label' => 'Aceite', 'color' => 'text-green-700', 'bg' => 'bg-green-50', 'ring' => 'ring-green-500',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'rejected' => ['label' => 'Rejeitado', 'color' => 'text-red-700', 'bg' => 'bg-red-50', 'ring' => 'ring-red-500',
                'icon' => 'm9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        ];
    @endphp

    <section class="mb-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
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

    @if (session('msg'))
        <p class="mb-3 rounded-sm border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('msg') }}</p>
    @endif

    {{-- pesquisa --}}

    <section class="mb-3 flex gap-3">
        <input wire:model.live.debounce.300ms="term" type="search" placeholder="Pesquisar por estado ou mensagem do comprovativo..."
            class="flex-1 rounded-sm border border-gray-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" />
    </section>

    {{-- tabela dos comprovativos --}}

    <section class="overflow-x-auto rounded-sm border border-gray-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">

            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-3 py-2 font-medium">Encomenda</th>
                    <th class="px-3 py-2 font-medium">Cliente</th>
                    <th class="px-3 py-2 font-medium">Comprovativo</th>
                    <th class="px-3 py-2 font-medium">Estado</th>
                    <th class="px-3 py-2 text-right font-medium">Acções</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($this->proofs() as $proof)
                    <tr wire:key="proof-{{ $proof->id }}" class="hover:bg-gray-50">

                        {{-- Encomenda + pagamento --}}
                        <td class="whitespace-nowrap px-3 py-2">
                            <p class="font-medium text-gray-800">Nº{{ $proof->order_id }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($proof->order->total, 0, ',', ' ') }} Kz</p>
                        </td>

                        {{-- Cliente --}}
                        <td class="px-3 py-2">
                            <p class="text-gray-800">{{ $proof->order->user?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $proof->order->user?->email }}</p>
                        </td>

                        {{-- Comprovativo --}}
                        
                        <td class="whitespace-nowrap px-3 py-2">
                            <p class="text-xs text-gray-500">{{ $proof->created_at->format('d/m/Y H:i') }}</p>
                            <div class="mt-1 flex gap-1.5">
                                {{-- heroicons: eye --}}
                                <a href="{{ route('payment-proofs.show', $proof) }}" target="_blank" rel="noopener" title="Abrir" aria-label="Abrir comprovativo"
                                    class="flex size-8 items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-blue-50 hover:text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>

                                {{-- heroicons: arrow-down-tray --}}
                                <button type="button" wire:click="download({{ $proof->id }})" title="Descarregar" aria-label="Descarregar comprovativo"
                                    class="flex size-8 cursor-pointer items-center justify-center rounded-full bg-gray-100 text-gray-600 transition hover:bg-green-50 hover:text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </button>
                            </div>
                        </td>

                        {{-- Estado + mensagem --}}
                        <td class="px-3 py-2">
                            <span @class([
                                'rounded-sm px-2 py-1 text-xs font-semibold',
                                'bg-yellow-100 text-yellow-700' => $proof->status === 'pending',
                                'bg-green-100 text-green-700' => $proof->status === 'accepted',
                                'bg-red-100 text-red-700' => $proof->status === 'rejected',
                            ])>
                                {{ $cards[$proof->status]['label'] ?? $proof->status }}
                            </span>
                            @if ($proof->msg)
                                <p class="mt-1 text-xs text-red-600">{{ $proof->msg }}</p>
                            @endif
                        </td>

                        {{-- Acções --}}
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            @if ($proof->status === 'pending')
                                <button wire:click="accept({{ $proof->id }})" wire:confirm="Aceitar este pagamento?"
                                    class="cursor-pointer rounded-sm bg-green-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-green-700">
                                    Aceitar
                                </button>
                                <button wire:click="openReject({{ $proof->id }})"
                                    class="cursor-pointer rounded-sm border border-gray-200 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                    Rejeitar
                                </button>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-gray-500">Nenhum comprovativo encontrado.</td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </section>

    {{-- janela: motivo da rejeição --}}

    @if ($rejectingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 p-4" wire:keydown.escape.window="cancelReject">
            <form wire:submit="reject" class="w-full max-w-md overflow-hidden rounded-sm bg-white shadow-xl">

                <header class="border-b border-gray-100 px-4 py-3">
                    <p class="font-semibold text-gray-900">Rejeitar comprovativo</p>
                    <p class="text-xs text-gray-500">O cliente vai ver este motivo e terá de enviar outro comprovativo.</p>
                </header>

                <div class="p-4">
                    <label for="msg" class="mb-1 block text-sm font-medium text-gray-700">Motivo da rejeição</label>
                    <textarea id="msg" wire:model="msg" rows="3" maxlength="255" autofocus
                        placeholder="Ex.: o valor transferido não corresponde ao total da encomenda."
                        class="w-full rounded-sm border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500"></textarea>
                    @error('msg') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
