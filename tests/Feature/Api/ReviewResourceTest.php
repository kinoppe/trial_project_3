<?php

namespace Tests\Feature\Api;

use App\Http\Resources\ReviewResource;
use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReviewResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_id_is_returned(): void
    {
        $review = Review::factory()->create();

        $data = (new ReviewResource($review))
            ->toArray(Request::create('/'));

        $this->assertEquals($review->id, $data['id']);
    }

    public function test_review_rating_is_returned(): void
    {
        $review = Review::factory()->create([
            'rating' => 5,
        ]);

        $data = (new ReviewResource($review))
            ->toArray(Request::create('/'));

        $this->assertEquals(5, $data['rating']);
    }

    public function test_review_comment_is_returned(): void
    {
        $review = Review::factory()->create([
            'comment' => 'とても面白い本でした。',
        ]);

        $data = (new ReviewResource($review))
            ->toArray(Request::create('/'));

        $this->assertEquals(
            'とても面白い本でした。',
            $data['comment']
        );
    }

    public function test_review_user_information_is_returned(): void
    {
        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = (new ReviewResource($review))
            ->toArray(Request::create('/'));

        $this->assertEquals($user->id, $data['user']['id']);

        $this->assertEquals('山田太郎', $data['user']['name']);
    }

    public function test_review_created_at_is_returned(): void
    {
        $review = Review::factory()->create();

        $data = (new ReviewResource($review))
            ->toArray(Request::create('/'));

        $this->assertEquals(
            $review->created_at->toISOString(),
            $data['created_at']
        );
    }

    public function test_review_updated_at_is_returned(): void
    {
        $review = Review::factory()->create();

        $data = (new ReviewResource($review))
            ->toArray(Request::create('/'));

        $this->assertEquals(
            $review->updated_at->toISOString(),
            $data['updated_at']
        );
    }

    public function test_multiple_reviews_can_be_transformed(): void
    {
        $reviews = Review::factory()->count(3)->create();

        $data = ReviewResource::collection($reviews)
            ->resolve(Request::create('/'));

        $this->assertCount(3, $data);

        foreach ($data as $review) {
            $this->assertArrayHasKey('id', $review);
            $this->assertArrayHasKey('rating', $review);
            $this->assertArrayHasKey('comment', $review);
            $this->assertArrayHasKey('user', $review);
            $this->assertArrayHasKey('created_at', $review);
            $this->assertArrayHasKey('updated_at', $review);
        }
    }
}
