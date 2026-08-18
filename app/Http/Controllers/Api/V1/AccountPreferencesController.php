<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountPreferencesController extends Controller
{
    public function notifications(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->notificationData($request->user('sanctum'))]);
    }

    public function updateNotifications(Request $request): JsonResponse
    {
        $data = $request->validate([
            'likes_enabled' => ['sometimes', 'boolean'],
            'comments_enabled' => ['sometimes', 'boolean'],
            'subscribers_enabled' => ['sometimes', 'boolean'],
            'system_enabled' => ['sometimes', 'boolean'],
        ]);
        $user = $request->user('sanctum');
        $user->update([
            ...collect($data)->mapWithKeys(fn ($value, $key) => ['notification_'.$key => $value])->all(),
        ]);

        return response()->json(['data' => $this->notificationData($user->fresh())]);
    }

    public function privacy(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->privacyData($request->user('sanctum'))]);
    }

    public function updatePrivacy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'channel_visibility' => ['sometimes', Rule::in(['public', 'private'])],
            'subscription_visibility' => ['sometimes', 'boolean'],
            'playlist_visibility' => ['sometimes', Rule::in(['public', 'private'])],
        ]);
        $user = $request->user('sanctum');
        $user->update($data);

        return response()->json(['data' => $this->privacyData($user->fresh())]);
    }

    private function notificationData(User $user): array
    {
        return [
            'likes_enabled' => (bool) $user->notification_likes_enabled,
            'comments_enabled' => (bool) $user->notification_comments_enabled,
            'subscribers_enabled' => (bool) $user->notification_subscribers_enabled,
            'system_enabled' => (bool) $user->notification_system_enabled,
        ];
    }

    private function privacyData(User $user): array
    {
        return [
            'channel_visibility' => $user->channel_visibility,
            'subscription_visibility' => (bool) $user->subscription_visibility,
            'playlist_visibility' => $user->playlist_visibility,
        ];
    }
}
