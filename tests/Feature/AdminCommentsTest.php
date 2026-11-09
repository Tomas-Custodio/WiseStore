<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_cards_show_total_month_and_most_commented_products(): void
    {
        $popular = Product::factory()->create(['name' => 'Produto Popular']);
        $quiet = Product::factory()->create(['name' => 'Produto Calmo']);
        Comment::factory()->count(3)->create(['product_id' => $popular->id]);
        Comment::factory()->create(['product_id' => $quiet->id]);
        Comment::factory()->create(['product_id' => $quiet->id, 'created_at' => now()->subMonths(2)]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('comments.index'))
            ->assertOk()
            ->assertSeeInOrder(['Total de comentários', '5', 'Este mês', '4', 'Mais comentados', '1. Produto Popular', '3', '2. Produto Calmo', '2']);
    }

    public function test_view_link_points_to_the_product_of_the_comment(): void
    {
        $product = Product::factory()->create();
        Comment::factory()->create(['product_id' => $product->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('comments.index'))
            ->assertSee(route('public.products.show', $product), false);
    }
}
