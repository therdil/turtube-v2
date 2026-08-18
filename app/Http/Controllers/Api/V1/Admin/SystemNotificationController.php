<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemNotificationController extends Controller
{
    /**
     * Server-authorized platform notification publisher. This route is admin
     * protected; a mobile client can never select another sender identity.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
            'user_id' => ['nullable', 'integer', 'exists:users,id', 'required_without:broadcast'],
            'broadcast' => ['nullable', 'boolean', 'required_without:user_id'],
        ]);

        $notification = new SystemNotification($data['title'], $data['message']);

        if (($data['broadcast'] ?? false) === true) {
            User::query()->select(['id', 'notification_system_enabled'])->each(
                fn (User $user) => $user->notify($notification)
            );

            return response()->json(['message' => 'Sistem bildirimi sıraya alındı.'], 202);
        }

        User::query()->findOrFail($data['user_id'])->notify($notification);

        return response()->json(['message' => 'Sistem bildirimi sıraya alındı.'], 202);
    }
}
