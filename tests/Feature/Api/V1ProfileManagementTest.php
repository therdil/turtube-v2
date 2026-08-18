<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class V1ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_only_their_own_username_and_channel(): void
    {
        $user = User::factory()->create(['name' => 'owner']);

        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/profile', [
            'username' => 'new.owner',
            'channel_name' => 'Yeni Kanal',
            'channel_description' => 'Gerçek kanal açıklaması',
        ])->assertOk()
            ->assertJsonPath('data.username', 'new.owner')
            ->assertJsonPath('data.display_name', 'Yeni Kanal');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'new.owner', 'channel_name' => 'Yeni Kanal']);
    }

    public function test_guest_cannot_update_a_profile_and_duplicate_username_is_rejected(): void
    {
        $owner = User::factory()->create(['name' => 'owner']);
        User::factory()->create(['name' => 'taken.name']);

        $this->patchJson('/api/v1/profile', ['username' => 'guest'])->assertUnauthorized();
        $this->actingAs($owner, 'sanctum')->patchJson('/api/v1/profile', ['username' => 'taken.name'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('username');
    }

    public function test_user_can_upload_and_remove_own_avatar_and_banner(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->post('/api/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ], ['Accept' => 'application/json'])->assertOk();
        $avatar = $user->fresh()->avatar;
        Storage::disk('public')->assertExists($avatar);

        $this->actingAs($user, 'sanctum')->post('/api/v1/profile/banner', [
            'banner' => UploadedFile::fake()->image('banner.webp'),
        ], ['Accept' => 'application/json'])->assertOk();
        $banner = $user->fresh()->banner;
        Storage::disk('public')->assertExists($banner);

        $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/profile/avatar')->assertOk()->assertJsonPath('data.avatar_url', null);
        $this->actingAs($user, 'sanctum')->deleteJson('/api/v1/profile/banner')->assertOk()->assertJsonPath('data.banner_url', null);
        Storage::disk('public')->assertMissing($avatar);
        Storage::disk('public')->assertMissing($banner);
    }

    public function test_invalid_image_and_wrong_current_password_are_rejected(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $this->actingAs($user, 'sanctum')->post('/api/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->create('not-image.pdf', 10, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertUnprocessable()->assertJsonValidationErrors('avatar');

        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/auth/password', [
            'current_password' => 'wrong-password',
            'password' => 'a-new-secure-password',
            'password_confirmation' => 'a-new-secure-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);

        $this->actingAs($user, 'sanctum')->patchJson('/api/v1/auth/password', [
            'current_password' => 'correct-password',
            'password' => 'a-new-secure-password',
            'password_confirmation' => 'a-new-secure-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('a-new-secure-password', $user->fresh()->password));
    }
}
