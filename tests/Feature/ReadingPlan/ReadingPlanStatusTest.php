<?php

namespace Tests\Feature\ReadingPlan;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_reading_plans_by_status(): void
    {
        $user = User::factory()->create();

        $planned = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Planned,
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
        ]);

        $response = $this->actingAs($user)->get(
            route('reading-plans.index', ['status' => 'planned'])
        );

        $response->assertOk();

        $response->assertViewHas('readingPlans', function ($plans) use ($planned) {
            return $plans->total() === 1
                && $plans->first()->id === $planned->id;
        });
    }

    public function test_user_can_complete_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Planned,
            'completed_at' => null,
        ]);

        $response = $this->actingAs($user)->post(
            route('reading-plans.complete', $readingPlan)
        );

        $response->assertSessionHas('success', '読書計画を完了しました。');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);

        $this->assertNotNull($readingPlan->fresh()->completed_at);
    }

    public function test_completed_reading_plan_cannot_be_completed_again(): void
    {
        $user = User::factory()->create();

        $completedAt = now()->subDay();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => $completedAt,
        ]);

        $response = $this->actingAs($user)->post(
            route('reading-plans.complete', $readingPlan)
        );

        $response->assertSessionHas(
            'success',
            'この書籍はすでに読了済みです。'
        );

        $this->assertEquals(
            $completedAt->timestamp,
            $readingPlan->fresh()->completed_at->timestamp
        );
    }

    public function test_invalid_status_filter_returns_validation_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(
            route('reading-plans.index', ['status' => 'invalid'])
        );

        $response->assertSessionHasErrors('status');
    }
}
