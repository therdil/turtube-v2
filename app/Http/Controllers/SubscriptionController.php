<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Kanala abone ol / abonelikten çık
     */
    public function toggle(Request $request, User $channel)
    {
        // Kendi kanalına abone olamaz
        if ($channel->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Kendi kanalınıza abone olamazsınız.',
            ], 422);
        }

        $subscription = Subscription::where('subscriber_id', auth()->id())
            ->where('channel_id', $channel->id)
            ->first();

        if ($subscription) {
            $subscription->delete();

            $subscribed = false;
        } else {
            Subscription::create([
                'subscriber_id' => auth()->id(),
                'channel_id' => $channel->id,
            ]);

            $subscribed = true;
        }

        return response()->json([
            'success' => true,
            'subscribed' => $subscribed,
            'subscribers_count' => Subscription::where('channel_id', $channel->id)->count(),
        ]);
    }
}