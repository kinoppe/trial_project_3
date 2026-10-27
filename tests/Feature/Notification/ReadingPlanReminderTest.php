<?php

namespace Tests\Feature\Notification;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanReminderTest extends TestCase
{
    use RefreshDatabase;

    private function createReadingPlan(): ReadingPlan
    {
        $user = User::factory()->create();

        $book = Book::factory()->create();

        return ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_notification_uses_database_channel(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'three_days_before'
        );

        $this->assertEquals(
            ['database'],
            $notification->via($plan->user)
        );
    }

    public function test_notification_three_days_before(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'three_days_before'
        );

        $data = $notification->toDatabase($plan->user);

        $this->assertEquals(
            '読書期限が近づいています',
            $data['title']
        );

        $this->assertEquals(
            "「{$plan->book->title}」の読書期限まであと3日です。",
            $data['body']
        );

        $this->assertEquals(
            'three_days_before',
            $data['timing']
        );
    }

    public function test_notification_on_target_date(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'on_target_date'
        );

        $data = $notification->toDatabase($plan->user);

        $this->assertEquals(
            '今日は読書期限です',
            $data['title']
        );

        $this->assertEquals(
            "「{$plan->book->title}」の読書期限は今日です。",
            $data['body']
        );

        $this->assertEquals(
            'on_target_date',
            $data['timing']
        );
    }

    public function test_notification_three_days_after(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'three_days_after'
        );

        $data = $notification->toDatabase($plan->user);

        $this->assertEquals(
            '読書期限を過ぎています',
            $data['title']
        );

        $this->assertEquals(
            "「{$plan->book->title}」の読書期限を3日過ぎています。",
            $data['body']
        );

        $this->assertEquals(
            'three_days_after',
            $data['timing']
        );
    }

    public function test_notification_with_unknown_timing(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'unknown'
        );

        $data = $notification->toDatabase($plan->user);

        $this->assertEquals(
            '読書計画のお知らせ',
            $data['title']
        );

        $this->assertEquals(
            "「{$plan->book->title}」の読書計画を確認してください。",
            $data['body']
        );
    }

    public function test_notification_contains_reading_plan_id(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'on_target_date'
        );

        $data = $notification->toDatabase($plan->user);

        $this->assertEquals(
            $plan->id,
            $data['reading_plan_id']
        );
    }

    public function test_notification_contains_book_id(): void
    {
        $plan = $this->createReadingPlan();

        $notification = new ReadingPlanReminder(
            $plan,
            'on_target_date'
        );

        $data = $notification->toDatabase($plan->user);

        $this->assertEquals(
            $plan->book_id,
            $data['book_id']
        );
    }

    public function test_notification_is_saved_to_database(): void
    {
        $plan = $this->createReadingPlan();

        $plan->user->notify(
            new ReadingPlanReminder($plan, 'on_target_date')
        );

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $plan->user_id,
            'type' => ReadingPlanReminder::class,
        ]);

        $notification = $plan->user->notifications()->first();

        $this->assertEquals(
            '今日は読書期限です',
            $notification->data['title']
        );

        $this->assertEquals(
            $plan->id,
            $notification->data['reading_plan_id']
        );
    }

    public function test_notification_is_sent_only_to_correct_user(): void
    {
        $plan = $this->createReadingPlan();

        $otherUser = User::factory()->create();

        $plan->user->notify(
            new ReadingPlanReminder($plan, 'on_target_date')
        );

        $this->assertEquals(
            1,
            $plan->user->notifications()->count()
        );

        $this->assertEquals(
            0,
            $otherUser->notifications()->count()
        );
    }
}
