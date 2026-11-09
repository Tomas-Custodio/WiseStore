<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_with_items_and_empties_cart(): void
    {
        $user = User::factory()->create();
        $shirt = Product::factory()->create(['name' => 'Camisola', 'preco' => 5000]);
        $cap = Product::factory()->create(['name' => 'Boné', 'preco' => 2000]);

        Cart::create(['user_id' => $user->id, 'product_id' => $shirt->id, 'quantity' => 2]);
        Cart::create(['user_id' => $user->id, 'product_id' => $cap->id, 'quantity' => 1]);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->call('checkout')
            ->assertSee('Encomenda feita com sucesso!');

        $order = $user->orders()->sole();

        $this->assertEquals(12000, $order->total);
        $this->assertSame('pending', $order->status);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_name' => 'Camisola', 'preco' => 5000, 'quantity' => 2]);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'product_name' => 'Boné', 'preco' => 2000, 'quantity' => 1]);
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_order_keeps_price_when_product_price_changes_later(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['preco' => 5000]);
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        Livewire::actingAs($user)->test('pages::cart.index')->call('checkout');

        $product->update(['preco' => 9000]);

        $this->assertEquals(5000, $user->orders()->sole()->items()->sole()->preco);
    }

    public function test_inactive_products_are_not_ordered(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->inactive()->create();
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        Livewire::actingAs($user)
            ->test('pages::cart.index')
            ->call('checkout')
            ->assertSee('Não há produtos disponíveis no carrinho.');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_only_uses_the_authenticated_users_cart(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::factory()->create();

        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);
        Cart::create(['user_id' => $otherUser->id, 'product_id' => $product->id, 'quantity' => 3]);

        Livewire::actingAs($user)->test('pages::cart.index')->call('checkout');

        $this->assertSame(1, $user->orders()->sole()->items()->sole()->quantity);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('carts', ['user_id' => $otherUser->id]);
    }

    public function test_orders_page_lists_only_the_authenticated_users_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->orders()->create(['total' => 5000])->items()->create(['product_name' => 'Camisola', 'preco' => 5000, 'quantity' => 1]);
        $otherUser->orders()->create(['total' => 2000])->items()->create(['product_name' => 'Boné', 'preco' => 2000, 'quantity' => 1]);

        $this->actingAs($user)
            ->get(route('public.encomendas.index'))
            ->assertOk()
            ->assertSee('Camisola')
            ->assertDontSee('Boné');
    }

    public function test_guest_is_redirected_from_orders_page(): void
    {
        $this->get(route('public.encomendas.index'))->assertRedirect();
    }

    public function test_cart_page_links_to_orders_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('public.cart.index'))
            ->assertOk()
            ->assertSee(route('public.encomendas.index'));
    }

    public function test_guest_is_redirected_from_cart_page_to_login(): void
    {
        $this->get(route('public.cart.index'))->assertRedirect(route('public.login'));
    }

    public function test_products_page_adds_one_unit_to_cart_each_click(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::public.product.index')
            ->call('addToCart', $product->id)
            ->call('addToCart', $product->id)
            ->assertSet('addedProductId', $product->id);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_products_page_cannot_add_inactive_product_to_cart(): void
    {
        $product = Product::factory()->inactive()->create();

        Livewire::actingAs(User::factory()->create())
            ->test('pages::public.product.index')
            ->call('addToCart', $product->id)
            ->assertNotFound();

        $this->assertDatabaseCount('carts', 0);
    }

    public function test_guest_adding_to_cart_from_products_page_is_sent_to_login(): void
    {
        $product = Product::factory()->create();

        Livewire::test('pages::public.product.index')
            ->call('addToCart', $product->id)
            ->assertRedirect(route('public.login'));

        $this->assertDatabaseCount('carts', 0);
    }
}
