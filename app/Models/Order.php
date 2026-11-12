<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = ['user_id', 'total', 'status', 'payment_method'];

    /**
     * Métodos de pagamento disponíveis no checkout.
     *
     * @var list<string>
     */
    public const PAYMENT_METHODS = [
        'Multicaixa Express',
        'Referência',
        'Transferência Bancária',
        'Unitel Money',
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
     * O comprovativo mais recente enviado pelo cliente.
     */
    public function paymentproof(): HasOne
    {
        return $this->hasOne(PaymentProof::class)->latestOfMany();
    }
}
