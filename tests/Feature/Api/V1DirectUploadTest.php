<?php

namespace Tests\Feature\Api;

use App\Jobs\ProcessUploadedVideo;
use App\Models\Category;
use App\Models\UploadBatch;
use App\Models\User;
use App\Models\UploadSession;
use App\Models\Video;
use App\Services\R2UploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class V1DirectUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_initiate_a_direct_upload(): void
    {
        $this->app->instance(R2UploadService::class, $this->uploadService([
            'key' => 'videos/11111111-1111-1111-1111-111111111111.mp4',
            'upload_url' => 'https://r2.example.test/presigned',
            'expires_in' => 900,
            'media_url' => 'https://cdn.example.test/videos/11111111-1111-1111-1111-111111111111.mp4',
        ]));

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/v1/uploads', ['extension' => 'mp4', 'content_type' => 'video/mp4'])
            ->assertCreated()
            ->assertJsonPath('upload.key', 'videos/11111111-1111-1111-1111-111111111111.mp4')
            ->assertJsonPath('upload.expires_in', 900)
            ->assertJsonPath('upload.session_id', 1);

        $this->assertDatabaseHas('upload_sessions', [
            'user_id' => User::query()->sole()->id,
            'object_key' => 'videos/11111111-1111-1111-1111-111111111111.mp4',
            'status' => UploadSession::STATUS_PENDING,
        ]);
    }

    public function test_guest_cannot_initiate_a_direct_upload(): void
    {
        $this->postJson('/api/v1/uploads', ['extension' => 'mp4', 'content_type' => 'video/mp4'])
            ->assertUnauthorized();
    }

    public function test_invalid_extension_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/v1/uploads', ['extension' => 'exe', 'content_type' => 'application/octet-stream'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['extension', 'content_type']);
    }

    public function test_invalid_content_type_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/v1/uploads', ['extension' => 'mp4', 'content_type' => 'image/jpeg'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content_type');
    }

    public function test_authenticated_user_can_complete_upload_when_objects_exist(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $category = $this->category();
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $thumbnailKey = 'thumbnails/22222222-2222-2222-2222-222222222222.jpg';
        $this->sessionsFor($user, $videoKey, $thumbnailKey);
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$videoKey, $thumbnailKey]));

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'video_key' => $videoKey,
            'thumbnail_key' => $thumbnailKey,
            'title' => 'Doğrudan R2 videosu',
            'category_id' => $category->id,
            'status' => 'private',
            'tags' => ['r2', 'mobil'],
        ])->assertCreated()
            ->assertJsonPath('data.video_url', url('/storage/'.$videoKey));

        $video = Video::query()->sole();
        $this->assertSame($user->id, $video->user_id);
        $this->assertSame($videoKey, $video->video_path);
        $this->assertSame('pending', $video->processing_status);
        $this->assertDatabaseHas('upload_sessions', ['object_key' => $videoKey, 'status' => UploadSession::STATUS_COMPLETED]);
        $this->assertDatabaseHas('upload_sessions', ['object_key' => $thumbnailKey, 'status' => UploadSession::STATUS_COMPLETED]);
        Queue::assertPushed(ProcessUploadedVideo::class);
    }

    public function test_invalid_video_key_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/v1/uploads/complete', [
                'video_key' => 'thumbnails/not-a-video.jpg',
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('video_key');
    }

    public function test_missing_video_object_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = $this->category();
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $this->sessionsFor($user, $videoKey);
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: []));

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'video_key' => $videoKey,
            'title' => 'Bulunamayan medya',
            'category_id' => $category->id,
            'status' => 'public',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('video_key');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_user_cannot_complete_another_users_upload(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $this->sessionsFor($owner, $videoKey);
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$videoKey]));

        $this->actingAs($attacker, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'video_key' => $videoKey,
            'title' => 'Yetkisiz video',
            'category_id' => $this->category()->id,
            'status' => 'public',
        ])->assertUnprocessable()->assertJsonValidationErrors('video_key');

        $this->assertDatabaseCount('videos', 0);
    }

    public function test_expired_upload_session_is_rejected(): void
    {
        $user = User::factory()->create();
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $this->sessionsFor($user, $videoKey, expiresAt: now()->subMinutes(3));
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$videoKey]));

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'video_key' => $videoKey,
            'title' => 'Süresi dolmuş video',
            'category_id' => $this->category()->id,
            'status' => 'public',
        ])->assertUnprocessable()->assertJsonValidationErrors('video_key');

        $this->assertDatabaseHas('upload_sessions', ['object_key' => $videoKey, 'status' => UploadSession::STATUS_EXPIRED]);
    }

    public function test_completed_upload_is_idempotent(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $category = $this->category();
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $this->sessionsFor($user, $videoKey, status: UploadSession::STATUS_COMPLETED);
        $video = Video::query()->create([
            'title' => 'İlk oluşturulan video',
            'video_path' => $videoKey,
            'channel_name' => $user->name,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'status' => 'public',
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'video_key' => $videoKey,
            'title' => 'Tekrarlanan istek',
            'category_id' => $category->id,
            'status' => 'public',
        ])->assertOk()->assertJsonPath('data.id', $video->id);

        $this->assertDatabaseCount('videos', 1);
        Queue::assertNothingPushed();
    }

    public function test_thumbnail_must_belong_to_authenticated_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $thumbnailKey = 'thumbnails/22222222-2222-2222-2222-222222222222.jpg';
        $this->sessionsFor($user, $videoKey);
        $this->sessionsFor($other, $thumbnailKey);
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$videoKey, $thumbnailKey]));

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'video_key' => $videoKey,
            'thumbnail_key' => $thumbnailKey,
            'title' => 'Yanlış thumbnail',
            'category_id' => $this->category()->id,
            'status' => 'public',
        ])->assertUnprocessable()->assertJsonValidationErrors('thumbnail_key');
    }

    public function test_strict_video_initiate_creates_a_batch_and_video_session(): void
    {
        $user = User::factory()->create();
        $this->app->instance(R2UploadService::class, $this->uploadService([
            'key' => 'videos/11111111-1111-1111-1111-111111111111.mp4',
            'upload_url' => 'https://r2.example.test/video', 'expires_in' => 900, 'media_url' => 'https://cdn.example.test/video',
        ]));

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', [
            'extension' => 'mp4', 'content_type' => 'video/mp4', 'kind' => 'video',
        ])->assertCreated();

        $batch = UploadBatch::query()->where('uuid', $response->json('upload.batch_id'))->sole();
        $this->assertSame($user->id, $batch->user_id);
        $this->assertDatabaseHas('upload_sessions', ['batch_id' => $batch->id, 'kind' => 'video']);
    }

    public function test_strict_thumbnail_joins_only_its_owners_pending_batch_once(): void
    {
        $user = User::factory()->create();
        $batch = $this->batchFor($user);
        $this->app->instance(R2UploadService::class, $this->uploadService([
            'key' => 'thumbnails/22222222-2222-2222-2222-222222222222.jpg',
            'upload_url' => 'https://r2.example.test/thumb', 'expires_in' => 900, 'media_url' => 'https://cdn.example.test/thumb',
        ]));

        $payload = ['extension' => 'jpg', 'content_type' => 'image/jpeg', 'kind' => 'thumbnail', 'batch_id' => $batch->uuid];
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', $payload)->assertCreated();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', $payload)->assertUnprocessable()->assertJsonValidationErrors('batch_id');

        $this->actingAs(User::factory()->create(), 'sanctum')->postJson('/api/v1/uploads', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('batch_id');
    }

    public function test_strict_mode_rejects_second_video_and_expired_batch_before_presigning(): void
    {
        $user = User::factory()->create();
        $batch = $this->batchFor($user);
        $this->app->instance(R2UploadService::class, $this->uploadService());

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', [
            'extension' => 'mp4', 'content_type' => 'video/mp4', 'kind' => 'video', 'batch_id' => $batch->uuid,
        ])->assertUnprocessable()->assertJsonValidationErrors('batch_id');

        $expired = $this->batchFor($user, now()->subMinutes(3));
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads', [
            'extension' => 'jpg', 'content_type' => 'image/jpeg', 'kind' => 'thumbnail', 'batch_id' => $expired->uuid,
        ])->assertUnprocessable()->assertJsonValidationErrors('batch_id');

        $this->assertDatabaseHas('upload_batches', ['id' => $expired->id, 'status' => UploadBatch::STATUS_EXPIRED]);
    }

    public function test_strict_complete_requires_same_batch_sessions_and_is_idempotent(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $batch = $this->batchFor($user);
        $otherBatch = $this->batchFor($user);
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $thumbnailKey = 'thumbnails/22222222-2222-2222-2222-222222222222.jpg';
        $this->sessionFor($user, $videoKey, $batch, 'video');
        $thumbnail = $this->sessionFor($user, $thumbnailKey, $otherBatch, 'thumbnail');
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$videoKey, $thumbnailKey]));
        $payload = ['batch_id' => $batch->uuid, 'video_key' => $videoKey, 'thumbnail_key' => $thumbnailKey, 'title' => 'Strict', 'category_id' => $this->category()->id, 'status' => 'private'];

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('thumbnail_key');

        $thumbnail->update(['batch_id' => $batch->id]);
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', $payload)->assertCreated();
        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', $payload)->assertOk();

        $this->assertDatabaseCount('videos', 1);
        $this->assertDatabaseHas('upload_batches', ['id' => $batch->id, 'status' => UploadBatch::STATUS_COMPLETED]);
        Queue::assertPushed(ProcessUploadedVideo::class, 1);
    }

    public function test_strict_complete_rejects_a_video_session_from_a_different_batch(): void
    {
        $user = User::factory()->create();
        $batch = $this->batchFor($user);
        $otherBatch = $this->batchFor($user);
        $videoKey = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $thumbnailKey = 'thumbnails/22222222-2222-2222-2222-222222222222.jpg';
        $this->sessionFor($user, $videoKey, $otherBatch, 'video');
        $this->sessionFor($user, $thumbnailKey, $batch, 'thumbnail');
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$videoKey, $thumbnailKey]));

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'batch_id' => $batch->uuid, 'video_key' => $videoKey, 'thumbnail_key' => $thumbnailKey,
            'title' => 'Yanlış video batch', 'category_id' => $this->category()->id, 'status' => 'public',
        ])->assertUnprocessable()->assertJsonValidationErrors('video_key');
    }

    public function test_strict_request_cannot_fall_back_to_legacy_or_use_expired_batch(): void
    {
        $user = User::factory()->create();
        $batch = $this->batchFor($user, now()->subMinutes(3));
        $key = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $this->sessionsFor($user, $key);
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$key]));

        $this->actingAs($user, 'sanctum')->postJson('/api/v1/uploads/complete', [
            'batch_id' => $batch->uuid, 'video_key' => $key, 'title' => 'No fallback', 'category_id' => $this->category()->id, 'status' => 'public',
        ])->assertUnprocessable()->assertJsonValidationErrors('batch_id');

        $this->assertDatabaseHas('upload_batches', ['id' => $batch->id, 'status' => UploadBatch::STATUS_EXPIRED]);
    }

    public function test_cleanup_dry_run_reports_expired_objects_without_deleting_or_touching_completed_or_active_sessions(): void
    {
        $user = User::factory()->create();
        $expired = 'videos/11111111-1111-1111-1111-111111111111.mp4';
        $missing = 'thumbnails/22222222-2222-2222-2222-222222222222.jpg';
        $completed = 'videos/33333333-3333-3333-3333-333333333333.mp4';
        $active = 'videos/44444444-4444-4444-4444-444444444444.mp4';
        $this->sessionsFor($user, $expired, null, now()->subHour(), UploadSession::STATUS_EXPIRED);
        $this->sessionsFor($user, $missing, null, now()->subHour(), UploadSession::STATUS_FAILED);
        $this->sessionsFor($user, $completed, null, now()->subHour(), UploadSession::STATUS_COMPLETED);
        $this->sessionsFor($user, $active, null, now()->addHour());
        $this->app->instance(R2UploadService::class, $this->uploadService(exists: [$expired]));

        $this->artisan('uploads:cleanup', ['--dry-run' => true, '--grace' => 0])
            ->expectsOutputToContain($expired)
            ->expectsOutputToContain($missing)
            ->expectsOutputToContain('Hiçbir R2 nesnesi silinmedi.')
            ->assertExitCode(0);
    }

    /** @param array{key: string, upload_url: string, expires_in: int, media_url: string}|null $upload */
    private function uploadService(?array $upload = null, array $exists = []): R2UploadService
    {
        $service = Mockery::mock(R2UploadService::class);

        if ($upload !== null) {
            $service->shouldReceive('createUpload')->once()->andReturn($upload);
        }

        $service->shouldReceive('exists')->zeroOrMoreTimes()->andReturnUsing(
            fn (string $key): bool => in_array($key, $exists, true),
        );

        return $service;
    }

    private function category(): Category
    {
        return Category::query()->create([
            'name' => 'R2 test '.Str::lower(Str::random(8)),
            'slug' => 'r2-test-'.Str::lower(Str::random(16)),
        ]);
    }

    private function sessionsFor(
        User $user,
        string $videoKey,
        ?string $thumbnailKey = null,
        ?\DateTimeInterface $expiresAt = null,
        string $status = UploadSession::STATUS_PENDING,
    ): void {
        foreach (array_filter([$videoKey, $thumbnailKey]) as $key) {
            $this->sessionFor($user, $key, null, null, $expiresAt, $status);
        }
    }

    private function batchFor(User $user, ?\DateTimeInterface $expiresAt = null): UploadBatch
    {
        return UploadBatch::query()->create([
            'uuid' => (string) Str::uuid(), 'user_id' => $user->id, 'status' => UploadBatch::STATUS_PENDING,
            'expires_at' => $expiresAt ?? now()->addMinutes(15),
        ]);
    }

    private function sessionFor(User $user, string $key, ?UploadBatch $batch = null, ?string $kind = null, ?\DateTimeInterface $expiresAt = null, string $status = UploadSession::STATUS_PENDING): UploadSession
    {
        return UploadSession::query()->create([
            'user_id' => $user->id, 'batch_id' => $batch?->id, 'object_key' => $key,
            'content_type' => str_starts_with($key, 'thumbnails/') ? 'image/jpeg' : 'video/mp4',
            'extension' => pathinfo($key, PATHINFO_EXTENSION), 'kind' => $kind, 'status' => $status,
            'expires_at' => $expiresAt ?? now()->addMinutes(15),
            'completed_at' => $status === UploadSession::STATUS_COMPLETED ? now() : null,
        ]);
    }
}
