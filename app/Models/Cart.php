<?php

namespace App\Models;

use Database\Factories\CartFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * Quantidade máxima de um mesmo produto no carrinho.
     */
    public const MAX_QUANTITY = 99;

    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Adiciona um produto ao carrinho. Se o produto já lá estiver,
     * soma a quantidade em vez de criar uma linha duplicada.
     */
    public function addProduct(Product $product, int $quantity = 1): CartItem
    {
        $item = $this->items()->firstOrNew(['product_id' => $product->id]);

        $item->quantity = min(($item->quantity ?? 0) + $quantity, self::MAX_QUANTITY);
        $item->save();

        return $item;
    }
}
