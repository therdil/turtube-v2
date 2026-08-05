<?php

use App\Models\User;
use App\Models\Video;
use App\Models\VideoReport;

function createPlatformVideo(User $owner, array $attributes = []): Video
{
    return Video::create(array_merge([
        'title' => 'Platform Test Videosu',
        'video_path' => 'videos/test.mp4',
        'channel_name' => $owner->name,
        'user_id' => $owner->id,
        'duration' => 0,
        'status' => 'public',
    ], $attributes));
}

test('shorts feed only exposes public shorts', function () {
    $owner = User::factory()->create();
    createPlatformVideo($owner, ['title' => 'Herkese Açık Short', 'is_short' => true]);
    createPlatformVideo($owner, ['title' => 'Normal Video', 'is_short' => false]);
    createPlatformVideo($owner, ['title' => 'Gizli Short', 'is_short' => true, 'status' => 'private']);

    $this->get(route('shorts.index'))
        ->assertOk()
        ->assertSee('Herkese Açık Short')
        ->assertDontSee('Normal Video')
        ->assertDontSee('Gizli Short');
});

test('admin panel is protected from non administrators', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('commenting on another channel creates a notification', function () {
    $owner = User::factory()->create();
    $commenter = User::factory()->create();
    $video = createPlatformVideo($owner);

    $this->actingAs($commenter)
        ->post(route('comments.store', $video), ['comment' => 'Harika bir video!'])
        ->assertRedirect(route('videos.show', $video));

    expect($owner->fresh()->notifications)->toHaveCount(1);
});

test('a user can report another users public video once', function () {
    $owner = User::factory()->create();
    $reporter = User::factory()->create();
    $video = createPlatformVideo($owner);

    $this->actingAs($reporter)
        ->post(route('videos.reports.store', $video), [
            'reason' => 'copyright',
            'details' => 'Bu içerik incelenmeli.',
        ])
        ->assertSessionHas('success');

    expect(VideoReport::query()->where('video_id', $video->id)->count())->toBe(1);
});

test('creator studio bulk action only updates the owners selected videos', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    $ownersVideo = createPlatformVideo($owner);
    $anotherUsersVideo = createPlatformVideo($anotherUser);

    $this->actingAs($owner)
        ->patch(route('studio.videos.bulk-update'), [
            'video_ids' => [$ownersVideo->id, $anotherUsersVideo->id],
            'status' => 'private',
        ])
        ->assertSessionHas('success');

    expect($ownersVideo->fresh()->status)->toBe('private')
        ->and($anotherUsersVideo->fresh()->status)->toBe('public');
});
