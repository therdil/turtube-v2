<?php

use App\Models\User;
use App\Models\Video;

function createVideoWithStatus(User $owner, string $title, string $status): Video
{
    return Video::create([
        'title' => $title,
        'video_path' => 'videos/test.mp4',
        'channel_name' => $owner->name,
        'user_id' => $owner->id,
        'duration' => 0,
        'status' => $status,
    ]);
}

test('private and draft videos are excluded from public discovery', function () {
    $owner = User::factory()->create();

    createVideoWithStatus($owner, 'Herkese Açık Video', 'public');
    createVideoWithStatus($owner, 'Gizli Video', 'private');
    createVideoWithStatus($owner, 'Taslak Video', 'draft');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Herkese Açık Video')
        ->assertDontSee('Gizli Video')
        ->assertDontSee('Taslak Video');
});

test('private and draft videos cannot be viewed by guests', function () {
    $owner = User::factory()->create();
    $privateVideo = createVideoWithStatus($owner, 'Gizli Video', 'private');
    $draftVideo = createVideoWithStatus($owner, 'Taslak Video', 'draft');

    $this->get(route('videos.show', $privateVideo))->assertNotFound();
    $this->get(route('videos.show', $draftVideo))->assertNotFound();
});

test('video owner can preview private and draft videos', function () {
    $owner = User::factory()->create();
    $privateVideo = createVideoWithStatus($owner, 'Gizli Video', 'private');
    $draftVideo = createVideoWithStatus($owner, 'Taslak Video', 'draft');

    $this->actingAs($owner)
        ->get(route('videos.show', $privateVideo))
        ->assertOk();

    $this->actingAs($owner)
        ->get(route('videos.show', $draftVideo))
        ->assertOk();
});

test('unlisted video is available only via its direct link', function () {
    $owner = User::factory()->create();
    $video = createVideoWithStatus($owner, 'Liste Dışı Video', 'unlisted');

    $this->get(route('home'))
        ->assertDontSee('Liste Dışı Video');

    $this->get(route('videos.show', $video))
        ->assertOk();
});
