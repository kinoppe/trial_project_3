<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestDatabaseNotification;
use Tests\TestCase;

class NotificationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_notifications(): void
    {
        $user = User::factory()->create();

        $user->notify(new TestDatabaseNotification());

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertViewIs('notifications.index');

        $response->assertViewHas('notifications', function ($notifications) {
            return $notifications->count() === 1;
        });
    }

    public function test_user_can_only_view_own_notifications(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->notify(new TestDatabaseNotification());
        $otherUser->notify(new TestDatabaseNotification());

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();

        $response->assertViewHas('notifications', function ($notifications) use ($user) {
            return $notifications->count() === 1
                && $notifications->first()->notifiable_id === $user->id;
        });
    }

    public function test_notifications_are_sorted_by_latest(): void
    {
        $user = User::factory()->create();

        $user->notify(new TestDatabaseNotification());
        $user->notify(new TestDatabaseNotification());

        $notifications = $user->notifications()->get();

        $olderNotification = $notifications->first();
        $newerNotification = $notifications->last();

        $olderNotification->update([
            'created_at' => now()->subDay(),
        ]);

        $newerNotification->update([
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('notifications.index'));

        $response->assertOk();

        $response->assertViewHas('notifications', function ($notifications) use ($newerNotification) {
            return $notifications->first()->id === $newerNotification->id;
        });
    }
}
