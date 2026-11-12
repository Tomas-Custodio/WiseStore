<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartItemsMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_cart_rows_are_moved_into_one_cart_per_user(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

        $this->assertFalse(Schema::hasTable('cart_items'));

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        [$shirt, $cap] = Product::factory()->count(2)->create();

        DB::table('carts')->insert([
            ['user_id' => $user->id, 'product_id' => $shirt->id, 'quantity' => 2],
            ['user_id' => $user->id, 'product_id' => $cap->id, 'quantity' => 1],
            ['user_id' => $otherUser->id, 'product_id' => $shirt->id, 'quantity' => 5],
        ]);

        $this->artisan('migrate')->assertSuccessful();

        $this->assertFalse(Schema::hasColumn('carts', 'product_id'));
        $this->assertDatabaseCount('carts', 2);
        $this->assertDatabaseCount('cart_items', 3);

        $this->assertEqualsCanonicalizing(
            [$shirt->id => 2, $cap->id => 1],
            $user->cart->items()->pluck('quantity', 'product_id')->all(),
        );
        $this->assertSame(5, $otherUser->cart->items()->sole()->quantity);
    }

    public function test_rollback_restores_old_rows_without_losing_items(): void
    {
        $user = User::factory()->create();
        [$shirt, $cap] = Product::factory()->count(2)->create();

        $cart = $user->cart()->create();
        $cart->addProduct($shirt, 2);
        $cart->addProduct($cap, 1);

        $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

        $this->assertFalse(Schema::hasTable('cart_items'));
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $shirt->id, 'quantity' => 2]);
        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $cap->id, 'quantity' => 1]);
    }
}
