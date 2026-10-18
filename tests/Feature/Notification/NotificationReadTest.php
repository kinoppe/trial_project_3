<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestDatabaseNotification;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();

        $user->notify(new TestDatabaseNotification());

        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)->post(
            route('notifications.read', $notification->id)
        );

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas(
            'success',
            '通知を既読にしました。'
        );

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_read_notification_remains_read(): void
    {
        $user = User::factory()->create();

        $user->notify(new TestDatabaseNotification());

        $notification = $user->notifications()->first();

        $notification->markAsRead();

        $readAt = $notification->fresh()->read_at;

        $response = $this->actingAs($user)->post(
            route('notifications.read', $notification->id)
        );

        $response->assertRedirect(route('notifications.index'));

        $this->assertEquals(
            $readAt->timestamp,
            $notification->fresh()->read_at->timestamp
        );
    }

    public function test_marking_one_notification_as_read_does_not_affect_others(): void
    {
        $user = User::factory()->create();

        $user->notify(new TestDatabaseNotification());
        $user->notify(new TestDatabaseNotification());

        $notifications = $user->notifications()->get();

        $firstNotification = $notifications->first();
        $secondNotification = $notifications->last();

        $response = $this->actingAs($user)->post(
            route('notifications.read', $firstNotification->id)
        );

        $response->assertRedirect(route('notifications.index'));

        $this->assertNotNull($firstNotification->fresh()->read_at);
        $this->assertNull($secondNotification->fresh()->read_at);
    }
}
