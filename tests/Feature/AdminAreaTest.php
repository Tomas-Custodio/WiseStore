<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_dashboard_with_store_summary(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Cliente Novo']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Produtos ativos')
            ->assertSee('Cliente Novo');
    }

    public function test_customers_page_lists_only_customers(): void
    {
        User::factory()->admin()->create(['name' => 'Outro Admin']);
        User::factory()->create(['name' => 'Maria Cliente']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('Maria Cliente')
            ->assertDontSee('Outro Admin');
    }

    public function test_team_page_lists_only_admins(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Chefe da Loja']);
        User::factory()->create(['name' => 'Maria Cliente']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Chefe da Loja')
            ->assertDontSee('Maria Cliente');
    }

    public function test_admin_can_block_and_unblock_customer(): void
    {
        $customer = User::factory()->create();

        $component = Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::customer.index')
            ->call('toggleBlock', $customer->id);

        $this->assertTrue($customer->fresh()->isBlocked());

        $component->call('toggleBlock', $customer->id);

        $this->assertFalse($customer->fresh()->isBlocked());
    }

    public function test_admin_cannot_block_another_admin_through_customers_page(): void
    {
        $otherAdmin = User::factory()->admin()->create();

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::customer.index')
            ->call('toggleBlock', $otherAdmin->id)
            ->assertNotFound();

        $this->assertFalse($otherAdmin->fresh()->isBlocked());
    }

    public function test_blocked_customer_cannot_login(): void
    {
        $customer = User::factory()->blocked()->create(['password' => 'secret123']);

        Livewire::test('pages::public.auth.login')
            ->set('email', $customer->email)
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_new_team_member_is_created_as_admin(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::user.create')
            ->set('name', 'Novo Admin')
            ->set('email', 'novo@loja.com')
            ->set('password', 'password123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'novo@loja.com', 'role' => 'admin']);
    }

    public function test_customer_cannot_be_edited_through_team_page(): void
    {
        $customer = User::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('users.edit', $customer))
            ->assertNotFound();
    }

    public function test_admin_edit_page_shows_profile_and_password_forms(): void
    {
        $teamMember = User::factory()->admin()->create(['name' => 'Ana Gestora', 'email' => 'ana@loja.com']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('users.edit', $teamMember))
            ->assertOk()
            ->assertSee('Ana Gestora')
            ->assertSee('ana@loja.com')
            ->assertSee('Guardar dados')
            ->assertSee('Alterar password');
    }

    public function test_team_page_search_filters_admins_by_name_or_email(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Chefe da Loja']);
        User::factory()->admin()->create(['name' => 'Ana Gestora', 'email' => 'ana@loja.com']);
        User::factory()->admin()->create(['name' => 'Bruno Vendas', 'email' => 'bruno@loja.com']);

        Livewire::actingAs($admin)
            ->test('pages::user.index')
            ->set('search', 'Ana')
            ->assertSee('Ana Gestora')
            ->assertDontSee('Bruno Vendas')
            ->set('search', 'bruno@')
            ->assertSee('Bruno Vendas')
            ->assertDontSee('Ana Gestora')
            ->set('search', 'ninguem')
            ->assertSee('Nenhum administrador encontrado.');
    }
}
