<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Feedback;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoReport;
use App\Notifications\AdminFeedbackNotification;
use App\Notifications\AdminVideoReportNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class V1FeedbackAndAdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_report_a_video_and_admin_is_notified(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $reporter = User::factory()->create();
        $video = $this->video();

        $this->actingAs($reporter, 'sanctum')->postJson("/api/v1/videos/{$video->id}/reports", ['reason' => 'spam', 'details' => 'Tekrarlanan bağlantılar'])
            ->assertCreated()->assertJsonPath('data.video_id', $video->id)->assertJsonPath('data.status', 'open');

        $this->assertDatabaseHas('video_reports', ['video_id' => $video->id, 'reporter_id' => $reporter->id, 'reason' => 'spam']);
        Notification::assertSentTo($admin, AdminVideoReportNotification::class);
    }

    public function test_guest_cannot_report_and_duplicate_report_is_rejected(): void
    {
        $video = $this->video();
        $reporter = User::factory()->create();
        $this->postJson("/api/v1/videos/{$video->id}/reports", ['reason' => 'spam'])->assertUnauthorized();
        $this->actingAs($reporter, 'sanctum')->postJson("/api/v1/videos/{$video->id}/reports", ['reason' => 'spam'])->assertCreated();
        $this->actingAs($reporter, 'sanctum')->postJson("/api/v1/videos/{$video->id}/reports", ['reason' => 'spam'])->assertStatus(409);
    }

    public function test_invalid_report_reason_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/v1/videos/'.$this->video()->id.'/reports', ['reason' => 'not-a-reason'])
            ->assertUnprocessable()->assertJsonValidationErrors('reason');
    }

    public function test_feedback_is_private_to_user_and_admin_can_review_it(): void
    {
        Notification::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $otherUser = User::factory()->create();
        $response = $this->actingAs($author, 'sanctum')->postJson('/api/v1/feedback', ['type' => 'suggestion', 'subject' => 'Arama filtresi', 'message' => 'Daha ayrıntılı filtre eklenmeli.'])
            ->assertCreated()->assertJsonPath('data.type', 'suggestion');
        $id = $response->json('data.id');
        $this->actingAs($otherUser, 'sanctum')->getJson('/api/v1/feedback')->assertOk()->assertJsonCount(0, 'data');
        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/feedback')->assertOk()->assertJsonPath('data.0.id', $id);
        $this->actingAs($admin, 'sanctum')->patchJson("/api/v1/admin/feedback/{$id}", ['status' => 'resolved', 'admin_note' => 'Planlandı.'])
            ->assertOk()->assertJsonPath('data.status', 'resolved')->assertJsonPath('data.admin_note', 'Planlandı.');
        Notification::assertSentTo($admin, AdminFeedbackNotification::class);
    }

    public function test_non_admin_cannot_access_admin_content_and_admin_can_moderate_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();
        $video = $this->video();
        $comment = Comment::query()->create(['video_id' => $video->id, 'user_id' => $user->id, 'comment' => 'İncelenecek yorum']);
        $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/videos')->assertForbidden();
        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/videos')->assertOk()->assertJsonPath('data.0.id', $video->id);
        $this->actingAs($admin, 'sanctum')->patchJson("/api/v1/admin/videos/{$video->id}", ['status' => 'private'])->assertOk();
        $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/comments/{$comment->id}")->assertNoContent();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_admin_category_slug_is_unique_and_category_with_videos_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $created = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/categories', ['name' => 'Test kategori'])->assertCreated();
        $categoryId = $created->json('data.id');
        $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/categories', ['name' => 'Test kategori'])->assertUnprocessable();
        $video = $this->video($categoryId);
        $this->actingAs($admin, 'sanctum')->deleteJson("/api/v1/admin/categories/{$video->category_id}")->assertUnprocessable();
    }

    private function video(?int $categoryId = null): Video
    {
        $owner = User::factory()->create();
        $category = $categoryId ? Category::query()->findOrFail($categoryId) : Category::query()->create(['name' => 'Kategori '.Str::random(8), 'slug' => 'kategori-'.Str::lower(Str::random(12))]);
        return Video::query()->create(['title' => 'Test video '.Str::random(8), 'video_path' => 'videos/test.mp4', 'channel_name' => $owner->name, 'user_id' => $owner->id, 'category_id' => $category->id, 'status' => 'public']);
    }
}
