<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_product_and_all_its_comments(): void
    {
        $product = Product::factory()->create(['name' => 'Galaxy S24']);
        Comment::factory()->count(12)->create(['product_id' => $product->id]);
        Comment::factory()->create(['product_id' => $product->id, 'body' => 'Bateria excelente']);
        Comment::factory()->create(['body' => 'Comentário de outro produto']);

        $this->get(route('public.products.show', $product))
            ->assertOk()
            ->assertSee('Galaxy S24')
            ->assertSee('Comentários (13)')
            ->assertSee('Bateria excelente')
            ->assertDontSee('Comentário de outro produto');
    }

    public function test_inactive_product_page_returns_not_found(): void
    {
        $product = Product::factory()->inactive()->create();

        $this->get(route('public.products.show', $product))->assertNotFound();
    }

    public function test_authenticated_customer_can_comment_on_product(): void
    {
        $product = Product::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test('pages::public.product.show', ['product' => $product])
            ->set('body', 'Chegou rápido!')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Chegou rápido!');

        $this->assertDatabaseHas('comments', ['product_id' => $product->id, 'body' => 'Chegou rápido!']);
    }

    public function test_guest_cannot_comment(): void
    {
        $product = Product::factory()->create();

        Livewire::test('pages::public.product.show', ['product' => $product])
            ->set('body', 'Spam')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_like_toggles_on_product_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $component = Livewire::actingAs($user)
            ->test('pages::public.product.show', ['product' => $product])
            ->call('toggleLike');

        $this->assertSame(1, $product->likes()->count());

        $component->call('toggleLike');

        $this->assertSame(0, $product->likes()->count());
    }

    public function test_product_page_shows_formatted_price(): void
    {
        $product = Product::factory()->create(['preco' => 29000]);

        $this->get(route('public.products.show', $product))
            ->assertOk()
            ->assertSee('29 000 Kz');
    }

    public function test_product_listings_link_to_the_details_page(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $detailsUrl = route('public.products.show', $product);

        $this->get(route('home'))->assertSee($detailsUrl);
        $this->get(route('public.products.index'))->assertSee($detailsUrl);
        $this->get(route('public.categories.show', $category))->assertSee($detailsUrl);
    }

    public function test_related_products_prefer_same_category_and_hide_inactive_ones(): void
    {
        $phones = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $phones->id]);
        Product::factory()->create(['name' => 'Galaxy A55', 'category_id' => $phones->id]);
        Product::factory()->inactive()->create(['name' => 'Modelo Antigo', 'category_id' => $phones->id]);

        $relatedNames = Livewire::test('pages::public.product.show', ['product' => $product])
            ->instance()->relatedProducts->pluck('name');

        $this->assertSame('Galaxy A55', $relatedNames->first());
        $this->assertNotContains('Modelo Antigo', $relatedNames);
        $this->assertNotContains($product->name, $relatedNames);
    }

    public function test_quantity_stays_between_one_and_ninety_nine(): void
    {
        $product = Product::factory()->create();

        Livewire::test('pages::public.product.show', ['product' => $product])
            ->call('decrementQuantity')
            ->assertSet('quantity', 1)
            ->set('quantity', 99)
            ->call('incrementQuantity')
            ->assertSet('quantity', 99);
    }

    public function test_customer_adds_product_to_cart_and_quantities_are_summed(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::public.product.show', ['product' => $product])
            ->call('incrementQuantity')
            ->call('addToCart')
            ->assertHasNoErrors()
            ->assertSet('quantity', 1)
            ->assertSee('Produto adicionado ao carrinho!')
            ->call('addToCart');

        $this->assertDatabaseHas('cart_product', [
            'cart_id' => $user->cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_guest_who_adds_to_cart_is_sent_to_login(): void
    {
        $product = Product::factory()->create();

        Livewire::test('pages::public.product.show', ['product' => $product])
            ->call('addToCart')
            ->assertRedirect(route('public.login'));

        $this->assertDatabaseCount('cart_product', 0);
    }
}
