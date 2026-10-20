<?php

namespace Tests\Unit\Policies;

use App\Models\Review;
use App\Models\User;
use App\Policies\ReviewPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_review(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertTrue(
            $policy->update($user, $review)
        );
    }

    public function test_other_user_cannot_update_review(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertFalse(
            $policy->update($otherUser, $review)
        );
    }

    public function test_owner_can_delete_review(): void
    {
        $user = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertTrue(
            $policy->delete($user, $review)
        );
    }

    public function test_other_user_cannot_delete_review(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReviewPolicy();

        $this->assertFalse(
            $policy->delete($otherUser, $review)
        );
    }
}
