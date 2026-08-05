<?php

use App\Models\LiveStream;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Str;

function releaseVideo(User $owner, array $attributes = []): Video
{
    return Video::create(array_merge([
        'title' => 'Yayın Öncesi Test Videosu',
        'video_path' => 'videos/test.mp4',
        'channel_name' => $owner->name,
        'user_id' => $owner->id,
        'duration' => 0,
        'status' => 'public',
    ], $attributes));
}

test('a creator cannot edit or delete another creators video', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    $video = releaseVideo($owner);

    $this->actingAs($anotherUser)
        ->get(route('videos.edit', $video))
        ->assertForbidden();

    $this->actingAs($anotherUser)
        ->put(route('videos.update', $video), [
            'title' => 'Yetkisiz değişiklik',
            'status' => 'public',
        ])
        ->assertForbidden();

    $this->actingAs($anotherUser)
        ->delete(route('videos.destroy', $video))
        ->assertForbidden();
});

test('premium videos reject non premium interactions', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $video = releaseVideo($owner, ['is_premium' => true]);

    $this->actingAs($viewer)
        ->post(route('videos.like', $video), [], ['Accept' => 'application/json'])
        ->assertNotFound();

    $this->actingAs($viewer)
        ->post(route('comments.store', $video), ['comment' => 'Erişememeliyim'])
        ->assertNotFound();
});

test('only the stream owner can start a live stream', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    $stream = $owner->liveStreams()->create([
        'title' => 'Test Yayını',
        'stream_key' => Str::random(40),
        'status' => 'scheduled',
    ]);

    $this->actingAs($anotherUser)
        ->post(route('live.start', $stream))
        ->assertForbidden();
});

test('protected mutation routes are rate limited', function () {
    $user = User::factory()->create();
    $video = releaseVideo(User::factory()->create());

    $this->actingAs($user);

    foreach (range(1, 15) as $attempt) {
        $this->post(route('comments.store', $video), ['comment' => 'Yorum '.$attempt]);
    }

    $this->post(route('comments.store', $video), ['comment' => 'Sınırı aşan yorum'])
        ->assertStatus(429);
});
