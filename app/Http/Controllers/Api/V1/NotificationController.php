<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max($request->integer('limit', 20), 1), 50);
        $user = $request->user('sanctum');

        return NotificationResource::collection(
            $user->notifications()->latest()->paginate($perPage)
        )->additional([
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user('sanctum')->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $request->user('sanctum')->notifications()->findOrFail($notification);

        if ($item->read_at === null) {
            $item->markAsRead();
        }

        return response()->json(['unread_count' => $request->user('sanctum')->unreadNotifications()->count()]);
    }
}
