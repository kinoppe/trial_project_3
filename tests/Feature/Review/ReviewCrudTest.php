<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_store_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
                'comment' => 'とても参考になりました。',
            ]
        );

        $response->assertRedirect(route('books.show', $book));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても参考になりました。',
        ]);
    }

    public function test_owner_can_edit_review(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(
            route('reviews.edit', $review)
        );

        $response->assertOk();
        $response->assertViewIs('reviews.edit');
        $response->assertViewHas('review');
    }

    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(
            route('reviews.update', $review),
            [
                'rating' => 4,
                'comment' => '更新したレビューです。',
            ]
        );

        $response->assertRedirect(
            route('books.show', $review->book_id)
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '更新したレビューです。',
        ]);
    }

    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->withoutExceptionHandling();

        $response = $this->actingAs($user)->delete(
            route('reviews.destroy', $review)
        );

        $response->assertRedirect(
            route('books.show', $book)
        );

        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }
}
