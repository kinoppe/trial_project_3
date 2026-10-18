<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_reading_plans(): void
    {
        $response = $this->get(route('reading-plans.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_create_reading_plan(): void
    {
        $response = $this->post(route('reading-plans.store'), [
            'book_id' => 1,
            'target_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_other_user_cannot_edit_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->get(
            route('reading-plans.edit', $readingPlan)
        );

        $response->assertForbidden();
    }

    public function test_other_user_cannot_update_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => now()->addDays(14)->toDateString(),
            ]
        );

        $response->assertForbidden();
    }

    public function test_other_user_cannot_delete_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser)->delete(
            route('reading-plans.destroy', $readingPlan)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }
}
