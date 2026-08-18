<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class NotificationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            'status' => ['nullable', Rule::in(['all', 'read', 'unread'])],
        ]);
        $perPage = $filters['limit'] ?? 20;
        $user = $request->user('sanctum');
        $notifications = $user->notifications()->latest();

        if (($filters['status'] ?? 'all') === 'read') {
            $notifications->whereNotNull('read_at');
        }

        if (($filters['status'] ?? 'all') === 'unread') {
            $notifications->whereNull('read_at');
        }

        return NotificationResource::collection(
            $notifications->paginate($perPage)->withQueryString()
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
