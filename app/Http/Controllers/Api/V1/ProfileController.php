<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuthenticatedUserResource;
use App\Models\User;
use App\Services\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Authenticated users can only change their own account and channel metadata. */
class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $data = $request->validate([
            'username' => [
                'sometimes', 'required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique(User::class, 'name')->ignore($user->id),
            ],
            'channel_name' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'channel_description' => ['nullable', 'string', 'max:1000'],
        ], [
            'username.regex' => 'Kullanıcı adı yalnızca harf, rakam, nokta, alt çizgi ve tire içerebilir.',
            'username.unique' => 'Bu kullanıcı adı zaten kullanılıyor.',
        ]);

        if (array_key_exists('username', $data)) {
            $data['name'] = $data['username'];
            unset($data['username']);
        }

        $user->fill($data);
        if ($user->isDirty()) {
            $user->save();
            ContentCache::flush();
        }

        return $this->response($user, 'Profil bilgileri güncellendi.');
    }

    public function storeAvatar(Request $request): JsonResponse
    {
        return $this->storeImage($request, 'avatar', 'avatars', 2048, 'Profil fotoğrafı güncellendi.');
    }

    public function destroyAvatar(Request $request): JsonResponse
    {
        return $this->removeImage($request, 'avatar', 'Profil fotoğrafı kaldırıldı.');
    }

    public function storeBanner(Request $request): JsonResponse
    {
        return $this->storeImage($request, 'banner', 'banners', 4096, 'Kanal bannerı güncellendi.');
    }

    public function destroyBanner(Request $request): JsonResponse
    {
        return $this->removeImage($request, 'banner', 'Kanal bannerı kaldırıldı.');
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'current_password.current_password' => 'Mevcut şifreniz yanlış.',
            'password.confirmed' => 'Yeni şifre tekrarı eşleşmiyor.',
        ]);

        $request->user('sanctum')->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Şifreniz başarıyla değiştirildi.']);
    }

    private function storeImage(Request $request, string $field, string $directory, int $maxKilobytes, string $message): JsonResponse
    {
        $request->validate([
            $field => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$maxKilobytes],
        ]);
        $user = $request->user('sanctum');
        $oldPath = $user->{$field};
        $path = $request->file($field)->store($directory, 'public');

        try {
            $user->update([$field => $path]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        if ($oldPath && str_starts_with($oldPath, $directory.'/')) {
            Storage::disk('public')->delete($oldPath);
        }
        ContentCache::flush();

        return $this->response($user->fresh(), $message);
    }

    private function removeImage(Request $request, string $field, string $message): JsonResponse
    {
        $user = $request->user('sanctum');
        $directory = $field === 'avatar' ? 'avatars/' : 'banners/';
        $path = $user->{$field};
        $user->update([$field => null]);
        if ($path && str_starts_with($path, $directory)) {
            Storage::disk('public')->delete($path);
        }
        ContentCache::flush();

        return $this->response($user->fresh(), $message);
    }

    private function response(User $user, string $message): JsonResponse
    {
        return (new AuthenticatedUserResource($user))
            ->additional(['message' => $message])
            ->response();
    }
}
