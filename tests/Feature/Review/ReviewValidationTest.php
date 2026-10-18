<?php

namespace Tests\Feature\Review;

use App\Models\Book;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_rating_is_required(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reviews.store', $book),
            [
                'comment' => 'レビュー本文',
            ]
        );

        $response->assertSessionHasErrors('rating');
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reviews.store', $book),
            [
                'rating' => 6,
                'comment' => 'レビュー本文',
            ]
        );

        $response->assertSessionHasErrors('rating');
    }

    public function test_comment_is_required(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
            ]
        );

        $response->assertSessionHasErrors('comment');
    }

    public function test_comment_cannot_exceed_1000_characters(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reviews.store', $book),
            [
                'rating' => 5,
                'comment' => str_repeat('あ', 1001),
            ]
        );

        $response->assertSessionHasErrors('comment');
    }

    public function test_update_rating_cannot_be_less_than_one(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(
            route('reviews.update', $review),
            [
                'rating' => 0,
                'comment' => '更新したレビューです。',
            ]
        );

        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'rating' => 0,
        ]);
    }

    public function test_update_rating_cannot_exceed_five(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(
            route('reviews.update', $review),
            [
                'rating' => 6,
                'comment' => '更新したレビューです。',
            ]
        );

        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
            'rating' => 6,
        ]);
    }

    public function test_update_comment_is_required(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(
            route('reviews.update', $review),
            [
                'rating' => 5,
                'comment' => '',
            ]
        );

        $response->assertSessionHasErrors('comment');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

}
