<?php

namespace Tests\Unit\Policies;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Policies\ReadingPlanPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReadingPlanPolicy();

        $this->assertTrue(
            $policy->update($user, $readingPlan)
        );
    }

    public function test_other_user_cannot_update_reading_plan(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReadingPlanPolicy();

        $this->assertFalse(
            $policy->update($otherUser, $readingPlan)
        );
    }

    public function test_owner_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReadingPlanPolicy();

        $this->assertTrue(
            $policy->delete($user, $readingPlan)
        );
    }

    public function test_other_user_cannot_delete_reading_plan(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReadingPlanPolicy();

        $this->assertFalse(
            $policy->delete($otherUser, $readingPlan)
        );
    }

    public function test_owner_can_complete_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $policy = new ReadingPlanPolicy();

        $this->assertTrue(
            $policy->complete($user, $readingPlan)
        );
    }

    public function test_other_user_cannot_complete_reading_plan(): void
    {
        $owner = User::factory()->create();

        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
        ]);

        $policy = new ReadingPlanPolicy();

        $this->assertFalse(
            $policy->complete($otherUser, $readingPlan)
        );
    }
}
