<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get(route('public.login'))->assertOk()->assertSee('Entrar');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        Livewire::test('pages::public.auth.login')
            ->set('email', $user->email)
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_is_redirected_to_dashboard_after_login(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'secret123']);

        Livewire::test('pages::public.auth.login')
            ->set('email', $admin->email)
            ->set('password', 'secret123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'secret123']);

        Livewire::test('pages::public.auth.login')
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('public.logout'))
            ->assertRedirect(route('public.login'));

        $this->assertGuest();
    }

    public function test_admin_creates_product_owned_by_them(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test('pages::product.create')
            ->assertSet('user_id', $admin->id)
            ->set('name', 'iPhone')
            ->set('preco', '850000')
            ->set('description', 'Telemóvel')
            ->set('img', UploadedFile::fake()->image('iphone.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['name' => 'iPhone', 'user_id' => $admin->id]);
    }

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('public.login'));
    }

    public function test_non_admin_user_is_forbidden_from_admin_area(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('products.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_area(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('products.index'))
            ->assertOk();
    }

    public function test_only_admin_sees_admin_panel_link(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('public.products.index'))
            ->assertDontSee('Painel admin');

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('public.products.index'))
            ->assertSee('Painel admin');
    }
}
