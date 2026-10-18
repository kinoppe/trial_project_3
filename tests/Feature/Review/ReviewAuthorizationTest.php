<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_store_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
                'comment' => 'ゲスト投稿',
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_other_user_cannot_edit_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->get(
            route('reviews.edit', $review)
        );

        $response->assertForbidden();
    }

    public function test_other_user_cannot_update_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->put(
            route('reviews.update', $review),
            [
                'rating' => 1,
                'comment' => '不正な更新',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'comment' => '不正な更新',
        ]);
    }

    public function test_other_user_cannot_delete_review(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(
            route('reviews.destroy', $review)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
