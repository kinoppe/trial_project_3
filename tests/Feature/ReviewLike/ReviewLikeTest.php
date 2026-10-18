<?php

namespace Tests\Feature\ReviewLike;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_like_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(
            route('books.show', $review->book_id)
        );

        $response->assertSessionHas(
            'success',
            'レビューにいいねしました。'
        );

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_user_can_unlike_review(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $user->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(
            route('books.show', $review->book_id)
        );

        $response->assertSessionHas(
            'success',
            'レビューのいいねを解除しました。'
        );

        $this->assertDatabaseMissing('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_toggle_does_not_create_duplicate_likes(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $this->assertDatabaseCount('review_likes', 1);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    public function test_user_like_does_not_affect_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create();

        $otherUser->likedReviews()->attach($review->id);

        $response = $this->actingAs($user)
            ->post(route('reviews.like', $review));

        $response->assertSessionHas(
            'success',
            'レビューにいいねしました。'
        );

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseHas('review_likes', [
            'user_id' => $otherUser->id,
            'review_id' => $review->id,
        ]);

        $this->assertDatabaseCount('review_likes', 2);
    }

    public function test_guest_cannot_like_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(
            route('reviews.like', $review)
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseCount('review_likes', 0);
    }

    public function test_nonexistent_review_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reviews.like', 999999)
        );

        $response->assertNotFound();
    }
}
