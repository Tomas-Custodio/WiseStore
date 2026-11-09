<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_product_with_image_stored_in_public_imgs(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::product.create')
            ->set('name', 'Galaxy S24')
            ->set('preco', '450000')
            ->set('description', 'Telemóvel Samsung')
            ->set('img', UploadedFile::fake()->image('galaxy.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::firstWhere('name', 'Galaxy S24');

        $this->assertSame('450000.00', $product->preco);
        $this->assertStringStartsWith('imgs/', $product->img);
        Storage::disk('public')->assertExists($product->img);
    }

    public function test_image_is_required(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::product.create')
            ->set('name', 'Sem Imagem')
            ->set('description', 'x')
            ->call('save')
            ->assertHasErrors(['img' => 'required']);

        $this->assertDatabaseMissing('products', ['name' => 'Sem Imagem']);
    }

    public function test_image_must_be_an_image_file(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::product.create')
            ->set('name', 'Ficheiro Errado')
            ->set('description', 'x')
            ->set('img', UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['img' => 'image']);
    }

    public function test_price_is_required_and_cannot_be_negative(): void
    {
        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::product.create')
            ->call('save')
            ->assertHasErrors(['preco' => 'required'])
            ->set('preco', '-10')
            ->call('save')
            ->assertHasErrors(['preco' => 'min']);
    }

    public function test_admin_edit_product_page_shows_current_data(): void
    {
        $product = Product::factory()->create(['name' => 'Galaxy S24']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('Editar produto')
            ->assertSee('Galaxy S24')
            ->assertSee('Guardar alterações');
    }

    public function test_admin_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Galaxy S24', 'preco' => 5000]);

        Livewire::actingAs(User::factory()->admin()->create())
            ->test('pages::product.edit', ['product' => $product])
            ->set('name', 'Galaxy S25')
            ->set('preco', '7000')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Produto atualizado!');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Galaxy S25', 'preco' => 7000]);
    }
}
