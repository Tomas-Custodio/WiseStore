<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_only_active_products_with_category_and_comment_count(): void
    {
        $category = Category::factory()->create(['name' => 'Telemóveis']);
        $product = Product::factory()->create(['name' => 'iPhone 15', 'category_id' => $category->id]);
        Comment::create(['product_id' => $product->id, 'body' => 'Muito bom']);
        Comment::create(['product_id' => $product->id, 'body' => 'Recomendo']);
        Product::factory()->inactive()->create(['name' => 'Produto Escondido']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('iPhone 15')
            ->assertSee('Telemóveis')
            ->assertSee('2 comentários')
            ->assertDontSee('Produto Escondido');
    }

    public function test_home_paginates_five_products_per_page(): void
    {
        Product::factory()->count(6)->create();

        Livewire::test('pages::public.index')
            ->tap(fn ($component) => $this->assertCount(5, $component->instance()->products->items()));
    }

    public function test_first_click_likes_and_second_click_removes_the_like(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $component = Livewire::actingAs($user)
            ->test('pages::public.index')
            ->call('toggleLike', $product->id);

        $this->assertDatabaseHas('likes', ['product_id' => $product->id, 'user_id' => $user->id]);

        $component->call('toggleLike', $product->id);

        $this->assertDatabaseMissing('likes', ['product_id' => $product->id, 'user_id' => $user->id]);
    }

    public function test_removing_a_like_only_deletes_the_users_own_like(): void
    {
        $product = Product::factory()->create();
        $otherLike = Like::factory()->create(['product_id' => $product->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test('pages::public.index')
            ->call('toggleLike', $product->id)
            ->call('toggleLike', $product->id);

        $this->assertModelExists($otherLike);
        $this->assertSame(1, $product->likes()->count());
    }

    public function test_guest_who_clicks_like_is_sent_to_login(): void
    {
        $product = Product::factory()->create();

        Livewire::test('pages::public.index')
            ->call('toggleLike', $product->id)
            ->assertRedirect(route('public.login'));

        $this->assertDatabaseCount('likes', 0);
    }
}
