<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    // Order

    protected $fillable = ['user_id', 'total', 'status', 'comprovativo'];

    /**
     * Estados da encomenda e o texto mostrado ao utilizador.
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        'pending' => 'Por pagar',
        'review' => 'Em análise',
        'paid' => 'Paga',
        'rejected' => 'Rejeitada',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Pagamento aceite pelo admin (no máximo um por encomenda).
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Todos os comprovativos enviados pelo cliente para esta encomenda.
     */
    public function paymentproofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    /**
     * O comprovativo mais recente enviado pelo cliente.
     */
    public function paymentproof(): HasOne
    {
        return $this->hasOne(PaymentProof::class)->latestOfMany();
    }

    /**
     * O cliente ainda pode enviar (ou reenviar) o comprovativo.
     */
    public function canSendProof(): bool
    {
        return in_array($this->status, ['pending', 'rejected'], true);
    }
}
