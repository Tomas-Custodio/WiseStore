<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separa o carrinho (carts) dos produtos que estão dentro dele (cart_items).
     *
     * Antes: cada linha de carts era um produto do carrinho (user_id, product_id, quantity).
     * Depois: carts tem uma linha por utilizador e os produtos ficam em cart_items.
     * Os dados existentes são copiados antes de qualquer coluna ser removida.
     *
     * A foreign key cart_items.cart_id só é criada no fim: alterar carts com ela já ativa
     * (no SQLite a tabela é recriada) apagaria em cascata os itens acabados de copiar.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });

        DB::transaction(function () {
            /** @var array<int, int> $cartIdByUser */
            $cartIdByUser = [];

            $legacyRows = DB::table('carts')
                ->orderBy('id')
                ->get(['id', 'user_id', 'product_id', 'quantity', 'created_at', 'updated_at']);

            foreach ($legacyRows as $row) {
                // A primeira linha de cada utilizador passa a ser o seu carrinho.
                $cartIdByUser[$row->user_id] ??= $row->id;

                DB::table('cart_items')->insert([
                    'cart_id' => $cartIdByUser[$row->user_id],
                    'product_id' => $row->product_id,
                    'quantity' => $row->quantity,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            // As restantes linhas já foram copiadas para cart_items.
            if ($cartIdByUser !== []) {
                DB::table('carts')->whereNotIn('id', array_values($cartIdByUser))->delete();
            }
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->unique('user_id');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropUnique(['user_id', 'product_id']);
            $table->dropColumn(['product_id', 'quantity']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
        });
    }

    /**
     * Volta à estrutura antiga (uma linha de carts por produto), sem perder itens.
     */
    public function down(): void
    {
        // Sem a foreign key, alterar carts não apaga os itens em cascata.
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1)->after('product_id');
        });

        // O índice composto é criado antes de remover o único em user_id (a foreign key de user_id precisa de um índice).
        Schema::table('carts', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id']);
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        DB::transaction(function () {
            foreach (DB::table('carts')->orderBy('id')->get(['id', 'user_id']) as $cart) {
                $items = DB::table('cart_items')->where('cart_id', $cart->id)->orderBy('id')->get();

                if ($items->isEmpty()) {
                    DB::table('carts')->where('id', $cart->id)->delete();

                    continue;
                }

                $first = $items->shift();

                DB::table('carts')->where('id', $cart->id)->update([
                    'product_id' => $first->product_id,
                    'quantity' => $first->quantity,
                ]);

                foreach ($items as $item) {
                    DB::table('carts')->insert([
                        'user_id' => $cart->user_id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ]);
                }
            }
        });

        Schema::dropIfExists('cart_items');
    }
};
