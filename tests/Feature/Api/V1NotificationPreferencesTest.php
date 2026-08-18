<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class V1NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_read_and_update_only_their_notification_preferences(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/v1/notification-preferences')
            ->assertOk()
            ->assertJsonPath('data.comments_enabled', true);

        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/notification-preferences', [
            'likes_enabled' => false,
            'comments_enabled' => false,
        ])->assertOk()
            ->assertJsonPath('data.likes_enabled', false)
            ->assertJsonPath('data.comments_enabled', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'notification_likes_enabled' => false,
            'notification_comments_enabled' => false,
        ]);
    }

    public function test_user_can_update_privacy_preferences_with_strict_values(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/privacy-settings', [
            'channel_visibility' => 'private',
            'subscription_visibility' => false,
            'playlist_visibility' => 'private',
        ])->assertOk()
            ->assertJsonPath('data.channel_visibility', 'private')
            ->assertJsonPath('data.subscription_visibility', false)
            ->assertJsonPath('data.playlist_visibility', 'private');

        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/privacy-settings', [
            'channel_visibility' => 'friends',
        ])->assertUnprocessable()->assertJsonValidationErrors('channel_visibility');
    }

    public function test_notification_feed_exposes_only_safe_navigation_targets_and_enforces_ownership(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = $owner->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'Tests\\Notifications\\Example',
            'data' => [
                'kind' => 'comment', 'title' => 'Yeni yorum', 'message' => 'İçerik',
                'video_id' => '12', 'comment_id' => '34', 'secret' => 'must-not-leak',
            ],
        ]);

        $this->actingAs($owner, 'sanctum')->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('data.0.video_id', '12')
            ->assertJsonPath('data.0.comment_id', '34')
            ->assertJsonMissing(['secret' => 'must-not-leak']);

        $this->actingAs($other, 'sanctum')->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertNotFound();
    }
}
