<?php

namespace Tests\Feature\Commands;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use App\Enums\ReadingPlanStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProcessReadingPlansTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-19 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function createPlan(string $date, string $status = 'planned'): ReadingPlan
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        return ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => $date,
            'status' => $status,
        ]);
    }

    public function test_expired_plan_becomes_overdue(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-18');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'overdue',
        ]);
    }

    public function test_plan_due_today_remains_planned(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-19');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        $this->assertSame(
            ReadingPlanStatus::Planned,
            $plan->fresh()->status
        );
    }

    public function test_future_plan_remains_planned(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-25');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        $this->assertSame(
            ReadingPlanStatus::Planned,
            $plan->fresh()->status
        );
    }

    public function test_completed_plan_is_not_updated(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-10', 'completed');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        $this->assertSame(
            ReadingPlanStatus::Completed,
            $plan->fresh()->status
        );

        Notification::assertNothingSent();
    }

    public function test_multiple_expired_plans_become_overdue(): void
    {
        Notification::fake();

        $first = $this->createPlan('2026-10-17');
        $second = $this->createPlan('2026-10-18');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        $this->assertSame(
            ReadingPlanStatus::Overdue,
            $first->fresh()->status
        );

        $this->assertSame(
            ReadingPlanStatus::Overdue,
            $second->fresh()->status
        );
    }

    public function test_command_succeeds_without_plans(): void
    {
        Notification::fake();

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_notification_is_sent_three_days_before(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-22');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $plan->user,
            ReadingPlanReminder::class
        );
    }

    public function test_notification_is_sent_on_due_date(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-19');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        Notification::assertSentTo(
            $plan->user,
            ReadingPlanReminder::class
        );
    }

    public function test_notification_is_sent_three_days_after(): void
    {
        Notification::fake();

        $plan = $this->createPlan('2026-10-16');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        $this->assertSame(
            ReadingPlanStatus::Overdue,
            $plan->fresh()->status
        );

        Notification::assertSentTo(
            $plan->user,
            ReadingPlanReminder::class
        );
    }

    public function test_notification_is_not_sent_on_other_days(): void
    {
        Notification::fake();

        $this->createPlan('2026-10-24');

        $this->artisan('reading-plans:process')
            ->assertExitCode(0);

        Notification::assertNothingSent();
    }
}
