<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_products_and_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::create([
            'img' => 'imgs/placeholder.jpg',
            'user_id' => User::factory()->create()->id,
            'category_id' => $category->id,
            'name' => 'iPhone',
            'description' => 'Telemóvel',
        ]);

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($product->category->is($category));
    }

    public function test_admin_can_create_category(): void
    {
        Livewire::test('pages::category.create')
            ->set('name', 'Acessórios')
            ->set('description', 'Capas e carregadores')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Acessórios', 'description' => 'Capas e carregadores']);
    }

    public function test_category_name_is_required_and_unique(): void
    {
        Category::factory()->create(['name' => 'Acessórios']);

        Livewire::test('pages::category.create')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required'])
            ->set('name', 'Acessórios')
            ->call('save')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_admin_can_edit_category_keeping_same_name(): void
    {
        $category = Category::factory()->create(['name' => 'Telemóveis']);

        Livewire::test('pages::category.edit', ['category' => $category])
            ->set('description', 'Nova descrição')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nova descrição', $category->fresh()->description);
    }

    public function test_deleting_category_keeps_products_without_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::create([
            'img' => 'imgs/placeholder.jpg',
            'user_id' => User::factory()->create()->id,
            'category_id' => $category->id,
            'name' => 'Cabo USB',
            'description' => 'Cabo',
        ]);

        Livewire::test('pages::category.index')->call('delete', $category->id);

        $this->assertModelMissing($category);
        $this->assertNull($product->fresh()->category_id);
    }

    public function test_admin_pages_render(): void
    {
        $category = Category::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('categories.index'))->assertOk()->assertSee($category->name);
        $this->get(route('categories.create'))->assertOk();
        $this->get(route('categories.edit', $category))->assertOk();
    }

    public function test_public_category_page_shows_only_active_products(): void
    {
        $category = Category::factory()->create();
        $userId = User::factory()->create()->id;
        Product::create(['img' => 'imgs/placeholder.jpg', 'user_id' => $userId, 'category_id' => $category->id, 'name' => 'Produto Visível', 'description' => 'x']);
        Product::create(['img' => 'imgs/placeholder.jpg', 'user_id' => $userId, 'category_id' => $category->id, 'name' => 'Produto Oculto', 'description' => 'x', 'status' => 'inactive']);

        $this->get(route('public.categories.index'))->assertOk()->assertSee($category->name);

        $this->get(route('public.categories.show', $category))
            ->assertOk()
            ->assertSee('Produto Visível')
            ->assertDontSee('Produto Oculto');
    }
}
