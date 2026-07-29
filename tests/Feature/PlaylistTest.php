<?php

use App\Models\Playlist;
use App\Models\User;
use App\Models\Video;

function makeVideoForPlaylist(User $user): Video
{
    return Video::create([
        'title' => 'Test videosu',
        'video_path' => 'videos/test.mp4',
        'channel_name' => $user->name,
        'user_id' => $user->id,
        'duration' => 0,
    ]);
}

test('authenticated user can create a playlist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('playlists.store'), [
        'name' => 'Favorilerim',
        'description' => 'İzlemek istediğim videolar',
        'is_public' => true,
    ]);

    $response->assertRedirect(route('playlists.index'));

    $this->assertDatabaseHas('playlists', [
        'user_id' => $user->id,
        'name' => 'Favorilerim',
        'is_public' => true,
    ]);
});

test('playlist owner can add and remove a video', function () {
    $user = User::factory()->create();
    $playlist = Playlist::create([
        'user_id' => $user->id,
        'name' => 'Favorilerim',
    ]);
    $video = makeVideoForPlaylist($user);

    $this->actingAs($user)
        ->postJson(route('playlists.toggle', $playlist), ['video_id' => $video->id])
        ->assertOk()
        ->assertJsonPath('added', true);

    $this->assertDatabaseHas('playlist_video', [
        'playlist_id' => $playlist->id,
        'video_id' => $video->id,
    ]);

    $this->actingAs($user)
        ->postJson(route('playlists.toggle', $playlist), ['video_id' => $video->id])
        ->assertOk()
        ->assertJsonPath('added', false);

    $this->assertDatabaseMissing('playlist_video', [
        'playlist_id' => $playlist->id,
        'video_id' => $video->id,
    ]);
});

test('user cannot change another users playlist', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $playlist = Playlist::create([
        'user_id' => $owner->id,
        'name' => 'Özel liste',
    ]);
    $video = makeVideoForPlaylist($owner);

    $this->actingAs($otherUser)
        ->postJson(route('playlists.toggle', $playlist), ['video_id' => $video->id])
        ->assertForbidden();
});
