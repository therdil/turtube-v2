<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Playlist;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V1PlaylistAndStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_list_and_add_then_remove_a_visible_video(): void
    {
        [$user, $video] = $this->userAndVideo();
        $create = $this->actingAs($user, 'sanctum')->postJson('/api/v1/playlists', [
            'name' => 'Android listem', 'description' => 'Gerçek API testi', 'is_public' => true,
        ])->assertCreated()->assertJsonPath('data.name', 'Android listem');

        $playlistId = (string) $create->json('data.id');
        $this->actingAs($user, 'sanctum')->postJson("/api/v1/playlists/{$playlistId}/videos", ['video_id' => $video->id])
            ->assertOk()->assertJsonPath('data.videos_count', 1);
        $this->actingAs($user, 'sanctum')->getJson("/api/v1/playlists/{$playlistId}")
            ->assertOk()->assertJsonPath('data.videos.0.id', $video->id);
        $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/playlists/{$playlistId}/videos/{$video->id}")
            ->assertOk();
    }

    public function test_user_cannot_change_another_users_playlist_and_can_read_own_analytics(): void
    {
        [$owner] = $this->userAndVideo();
        $other = User::factory()->create();
        $playlist = Playlist::query()->create(['user_id' => $owner->id, 'name' => 'Özel liste']);

        $this->actingAs($other, 'sanctum')->patchJson("/api/v1/playlists/{$playlist->id}", ['name' => 'Yetkisiz'])
            ->assertForbidden();
        $this->actingAs($owner, 'sanctum')->getJson('/api/v1/studio/analytics')
            ->assertOk()->assertJsonPath('data.stats.videos', 1)->assertJsonPath('data.stats.views', 0);
    }

    /** @return array{User, Video} */
    private function userAndVideo(): array
    {
        $user = User::factory()->create();
        $category = Category::query()->create(['name' => 'Test Kategori', 'slug' => 'test-kategori']);
        $video = Video::query()->create([
            'title' => 'Playlist API videosu', 'video_path' => 'videos/test.mp4', 'channel_name' => $user->name,
            'duration' => 60, 'views' => 0, 'user_id' => $user->id, 'category_id' => $category->id,
            'status' => 'public', 'is_short' => false, 'is_premium' => false,
        ]);

        return [$user, $video];
    }
}
