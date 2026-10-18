<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestDatabaseNotification;
use Tests\TestCase;

class NotificationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_view_notifications(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $owner->notify(new TestDatabaseNotification());

        $notification = $owner->notifications()->first();

        $response = $this->actingAs($otherUser)->post(
            route('notifications.read', $notification->id)
        );

        $response->assertNotFound();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_nonexistent_notification_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(
            route('notifications.read', 'nonexistent-id')
        );

        $response->assertNotFound();
    }
}
