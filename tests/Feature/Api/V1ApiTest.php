<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V1ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_video_endpoints_return_only_their_expected_content_type(): void
    {
        [$user, $category] = $this->channelAndCategory();
        $video = $this->makeVideo($user, $category, ['title' => 'Uzun video']);
        $short = $this->makeVideo($user, $category, ['title' => 'Kisa video', 'is_short' => true]);
        $this->makeVideo($user, $category, ['title' => 'Premium video', 'is_premium' => true]);

        $this->getJson('/api/v1/videos?category=bilim')
            ->assertOk()
            ->assertJsonPath('data.0.id', $video->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_short', false)
            ->assertJsonPath('data.0.playback_sources.0.label', 'Orijinal');

        $this->getJson('/api/v1/shorts')
            ->assertOk()
            ->assertJsonPath('data.0.id', $short->id)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_short', true);
    }

    public function test_video_detail_categories_search_and_channel_are_available_as_json(): void
    {
        [$user, $category] = $this->channelAndCategory();
        $video = $this->makeVideo($user, $category, ['title' => 'Laravel ile API gelistirme', 'views' => 42]);

        $this->getJson("/api/v1/videos/{$video->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $video->id)
            ->assertJsonPath('data.channel.username', $user->name)
            ->assertJsonPath('data.category.slug', 'bilim');

        $this->getJson('/api/v1/categories')
            ->assertOk()
            ->assertJsonFragment(['slug' => 'bilim']);

        $this->getJson('/api/v1/search?q=Laravel')
            ->assertOk()
            ->assertJsonPath('data.0.id', $video->id);

        $this->getJson('/api/v1/channels/turtube-test')
            ->assertOk()
            ->assertJsonPath('channel.username', $user->name)
            ->assertJsonPath('data.0.id', $video->id);
    }

    public function test_authentication_issues_and_revokes_a_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'api@example.test',
            'name' => 'api-user',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'API test device',
        ])
            ->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.email', $user->email);

        $token = $login->json('data.token');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        app('auth')->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    /** @return array{User, Category} */
    private function channelAndCategory(): array
    {
        $user = User::factory()->create([
            'name' => 'turtube-test',
            'channel_name' => 'TurTube Test',
        ]);
        $category = Category::query()->firstOrCreate(['slug' => 'bilim'], ['name' => 'Bilim']);

        return [$user, $category];
    }

    private function makeVideo(User $user, Category $category, array $attributes = []): Video
    {
        return Video::query()->create([
            'title' => 'Test videosu',
            'description' => 'API test videosu aciklamasi.',
            'video_path' => 'videos/test.mp4',
            'thumbnail' => 'thumbnails/test.jpg',
            'channel_name' => $user->channel_name ?: $user->name,
            'duration' => 60,
            'views' => 0,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'status' => 'public',
            'is_short' => false,
            'is_premium' => false,
            ...$attributes,
        ]);
    }
}
