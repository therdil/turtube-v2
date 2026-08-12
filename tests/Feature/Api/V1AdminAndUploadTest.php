<?php

namespace Tests\Feature\Api;

use App\Jobs\ProcessUploadedVideo;
use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class V1AdminAndUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_is_exposed_only_to_the_authenticated_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/auth/me')
            ->assertOk()->assertJsonPath('data.role', 'admin')->assertJsonPath('data.is_admin', true);
    }

    public function test_normal_user_cannot_access_admin_user_management(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_moderator_cannot_change_roles(): void
    {
        $moderator = User::factory()->create(['name' => 'moderator-user', 'is_moderator' => true]);
        $target = User::factory()->create(['name' => 'target-user']);

        $this->actingAs($moderator, 'sanctum')->patchJson("/api/v1/admin/users/{$target->name}", ['role' => 'admin'])->assertForbidden();
        $this->assertFalse($target->fresh()->is_admin);
    }

    public function test_moderator_can_review_reports_without_receiving_admin_access(): void
    {
        $moderator = User::factory()->create(['is_moderator' => true]);
        $owner = User::factory()->create();
        $reporter = User::factory()->create();
        $category = $this->makeCategory('Haber');
        $video = Video::query()->create([
            'title' => 'İncelenecek video', 'video_path' => 'videos/report.mp4', 'channel_name' => $owner->name,
            'user_id' => $owner->id, 'category_id' => $category->id, 'status' => 'public',
        ]);
        $report = VideoReport::query()->create(['video_id' => $video->id, 'reporter_id' => $reporter->id, 'reason' => 'spam']);

        $this->actingAs($moderator, 'sanctum')->getJson('/api/v1/moderation/reports')->assertOk();
        $this->actingAs($moderator, 'sanctum')->patchJson("/api/v1/moderation/reports/{$report->id}", ['status' => 'resolved'])
            ->assertOk()->assertJsonPath('data.status', 'resolved');
        $this->actingAs($moderator, 'sanctum')->getJson('/api/v1/admin/users')->assertForbidden();
    }

    public function test_admin_can_assign_moderator_and_admin_roles(): void
    {
        $admin = User::factory()->create(['name' => 'admin-user', 'is_admin' => true]);
        $target = User::factory()->create(['name' => 'target-user']);

        $this->actingAs($admin, 'sanctum')->patchJson("/api/v1/admin/users/{$target->name}", ['role' => 'moderator'])
            ->assertOk()->assertJsonPath('data.role', 'moderator');
        $this->assertTrue($target->fresh()->is_moderator);

        $this->actingAs($admin, 'sanctum')->patchJson("/api/v1/admin/users/{$target->name}", ['role' => 'admin'])
            ->assertOk()->assertJsonPath('data.role', 'admin');
        $this->assertTrue($target->fresh()->is_admin);
        $this->assertFalse($target->fresh()->is_moderator);
    }

    public function test_admin_can_read_server_authoritative_dashboard_metrics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();
        $category = $this->makeCategory('Dashboard');
        Video::factory()->for($owner)->for($category)->create(['views' => 12, 'is_short' => false]);
        Video::factory()->for($owner)->for($category)->create(['views' => 5, 'is_short' => true]);

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonPath('data.videos', 1)
            ->assertJsonPath('data.shorts', 1)
            ->assertJsonPath('data.views', 17);
    }

    public function test_guest_cannot_upload_video(): void
    {
        $this->postJson('/api/v1/videos')->assertUnauthorized();
    }

    public function test_authenticated_user_uploads_a_video_as_their_own_content(): void
    {
        Storage::fake(config('video.disk'));
        Queue::fake();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $category = $this->makeCategory('Eğitim');

        $response = $this->actingAs($user, 'sanctum')->post('/api/v1/videos', [
            'title' => 'Mobil video', 'category_id' => $category->id, 'status' => 'private',
            'video' => UploadedFile::fake()->create('video.mp4', 1024, 'video/mp4'), 'user_id' => $other->id,
        ], ['Accept' => 'application/json']);

        $response->assertCreated()->assertJsonPath('data.title', 'Mobil video')->assertJsonPath('data.is_short', false);
        $video = Video::query()->sole();
        $this->assertSame($user->id, $video->user_id);
        Storage::disk(config('video.disk'))->assertExists($video->video_path);
        Queue::assertPushed(ProcessUploadedVideo::class);
    }

    public function test_invalid_video_is_rejected_as_json_without_creating_media(): void
    {
        Storage::fake(config('video.disk'));
        $user = User::factory()->create();
        $category = $this->makeCategory('Bilim');

        $this->actingAs($user, 'sanctum')->post('/api/v1/videos', [
            'title' => 'Geçersiz dosya', 'category_id' => $category->id, 'status' => 'public',
            'video' => UploadedFile::fake()->create('zararlı.exe', 32, 'application/octet-stream'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('video');

        $this->assertDatabaseCount('videos', 0);
        Storage::disk(config('video.disk'))->assertDirectoryEmpty('videos');
    }

    public function test_authenticated_user_uploads_a_short_using_the_same_secure_pipeline(): void
    {
        Storage::fake(config('video.disk'));
        Queue::fake();
        $user = User::factory()->create();
        $category = $this->makeCategory('Müzik');

        $this->actingAs($user, 'sanctum')->post('/api/v1/shorts', [
            'title' => 'Mobil short', 'category_id' => $category->id, 'status' => 'public',
            'video' => UploadedFile::fake()->create('short.mp4', 1024, 'video/mp4'),
        ], ['Accept' => 'application/json'])->assertCreated()->assertJsonPath('data.is_short', true);

        $this->assertTrue(Video::query()->sole()->is_short);
    }

    private function makeCategory(string $name): Category
    {
        return Category::query()->create([
            'name' => $name.' API testi '.Str::lower(Str::random(8)),
            'slug' => 'api-test-'.Str::lower(Str::random(16)),
        ]);
    }
}
