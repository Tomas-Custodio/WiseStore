<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('public.profile'))->assertRedirect(route('public.login'));
    }

    public function test_customer_sees_own_profile(): void
    {
        $customer = User::factory()->create(['name' => 'Ana Silva']);

        $this->actingAs($customer)
            ->get(route('public.profile'))
            ->assertOk()
            ->assertSee('O meu perfil')
            ->assertSee('Ana Silva');
    }

    public function test_customer_updates_name_and_email(): void
    {
        $customer = User::factory()->create();

        Livewire::actingAs($customer)
            ->test('pages::public.user.edit')
            ->set('name', 'Novo Nome')
            ->set('email', 'novo@email.com')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $customer->refresh();
        $this->assertSame('Novo Nome', $customer->name);
        $this->assertSame('novo@email.com', $customer->email);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'ocupado@email.com']);

        Livewire::actingAs(User::factory()->create())
            ->test('pages::public.user.edit')
            ->set('email', 'ocupado@email.com')
            ->call('saveProfile')
            ->assertHasErrors('email');
    }

    public function test_customer_changes_password_with_current_password(): void
    {
        $customer = User::factory()->create(['password' => 'antiga123']);

        Livewire::actingAs($customer)
            ->test('pages::public.user.edit')
            ->set('current_password', 'antiga123')
            ->set('password', 'nova-password')
            ->set('password_confirmation', 'nova-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('nova-password', $customer->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $customer = User::factory()->create(['password' => 'antiga123']);

        Livewire::actingAs($customer)
            ->test('pages::public.user.edit')
            ->set('current_password', 'errada')
            ->set('password', 'nova-password')
            ->set('password_confirmation', 'nova-password')
            ->call('updatePassword')
            ->assertHasErrors('current_password');

        $this->assertTrue(Hash::check('antiga123', $customer->fresh()->password));
    }
}
