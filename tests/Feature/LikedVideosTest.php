<?php

use App\Models\User;
use App\Models\Video;

function createLikedVideo(User $user, string $title): Video
{
    return Video::create([
        'title' => $title,
        'video_path' => 'videos/test.mp4',
        'channel_name' => $user->name,
        'user_id' => $user->id,
        'duration' => 0,
    ]);
}

test('guests are redirected away from liked videos', function () {
    $this->get(route('liked-videos.index'))
        ->assertRedirect(route('login'));
});

test('liked videos page only shows the authenticated users likes', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $likedVideo = createLikedVideo($otherUser, 'Beğenilen video');
    $otherVideo = createLikedVideo($otherUser, 'Başka kullanıcının videosu');

    $user->likedVideos()->attach($likedVideo);
    $otherUser->likedVideos()->attach($otherVideo);

    $this->actingAs($user)
        ->get(route('liked-videos.index'))
        ->assertOk()
        ->assertSee('Beğenilen video')
        ->assertDontSee('Başka kullanıcının videosu');
});
