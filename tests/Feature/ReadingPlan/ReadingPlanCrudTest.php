<?php

namespace Tests\Feature\ReadingPlan;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_reading_plans(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.index'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.index');
        $response->assertViewHas('readingPlans');
    }

    public function test_user_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('reading-plans.create'));

        $response->assertOk();
        $response->assertViewIs('reading-plans.create');
        $response->assertViewHas('books');
    }

    public function test_user_can_create_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(
            route('reading-plans.store'),
            [
                'book_id' => $book->id,
                'target_date' => now()->addDays(7)->toDateString(),
            ]
        );

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を登録しました。');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::Planned->value,
        ]);
    }

    public function test_user_can_view_edit_page(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('reading-plans.edit', $readingPlan));

        $response->assertOk();
        $response->assertViewIs('reading-plans.edit');
        $response->assertViewHas('readingPlan');
    }

    public function test_user_can_update_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $newDate = now()->addDays(14)->toDateString();

        $response = $this->actingAs($user)->put(
            route('reading-plans.update', $readingPlan),
            [
                'target_date' => $newDate,
            ]
        );

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を更新しました。');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => $newDate,
            'status' => ReadingPlanStatus::Planned->value,
        ]);
    }

    public function test_user_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();

        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(
            route('reading-plans.destroy', $readingPlan)
        );

        $response->assertRedirect(route('reading-plans.index'));
        $response->assertSessionHas('success', '読書計画を削除しました。');

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }
}
