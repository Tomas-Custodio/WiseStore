<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = ['user_id', 'order_id', 'doc', 'status', 'rejection_reason', 'rejection_note'];

    /**
     * Todo o comprovativo novo começa à espera da aprovação.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * Estados do comprovativo e o texto mostrado.
     *
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        'pending' => 'À espera da aprovação',
        'verified' => 'Verificado',
        'rejected' => 'Rejeitado',
    ];

    /**
     * Motivos possíveis para rejeitar um comprovativo.
     *
     * @var array<string, string>
     */
    public const REJECTION_REASONS = [
        'burla' => 'Burla',
        'falsificacao' => 'Falsificação do documento',
        'valor_incorreto' => 'Valor transferido incorreto',
        'conta_errada' => 'Transferência para a conta errada',
        'ilegivel' => 'Documento ilegível',
        'outro' => 'Outro motivo',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Texto do motivo da rejeição (ou null se não foi rejeitado).
     */
    public function rejectionReasonLabel(): ?string
    {
        return self::REJECTION_REASONS[$this->rejection_reason] ?? null;
    }
}
