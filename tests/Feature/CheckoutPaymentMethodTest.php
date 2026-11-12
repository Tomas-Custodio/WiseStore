<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CheckoutPaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private function customerWithCart(): User
    {
        $customer = User::factory()->create();

        $customer->cart()->firstOrCreate()->items()->create([
            'product_id' => Product::factory()->create()->id,
            'quantity' => 2,
        ]);

        return $customer;
    }

    public function test_checkout_requires_a_payment_method(): void
    {
        $customer = $this->customerWithCart();

        Livewire::actingAs($customer)
            ->test('pages::public.checkout')
            ->call('finalizar')
            ->assertHasErrors(['paymentMethod' => 'required']);

        $this->assertSame(0, $customer->orders()->count());
        $this->assertSame(1, $customer->cartItems()->count());
    }

    public function test_checkout_rejects_an_unknown_payment_method(): void
    {
        $customer = $this->customerWithCart();

        Livewire::actingAs($customer)
            ->test('pages::public.checkout')
            ->set('paymentMethod', 'bitcoin')
            ->call('finalizar')
            ->assertHasErrors(['paymentMethod' => 'in']);

        $this->assertSame(0, $customer->orders()->count());
    }

    public function test_order_stores_the_chosen_payment_method(): void
    {
        $customer = $this->customerWithCart();

        Livewire::actingAs($customer)
            ->test('pages::public.checkout')
            ->set('paymentMethod', 'Multicaixa Express')
            ->call('finalizar')
            ->assertHasNoErrors()
            ->assertRedirect(route('public.encomendas.index'));

        $order = $customer->orders()->sole();

        $this->assertSame('Multicaixa Express', $order->payment_method);
        $this->assertSame(0, $customer->cartItems()->count());
        $this->assertNotNull($customer->cart()->first());
    }
}
