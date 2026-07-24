<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;

class ChannelController extends Controller
{
    /**
     * Kanal sayfasını göster
     */
    public function show(User $user)
    {
        $videos = $user->videos()
            ->latest()
            ->get();

        $subscribersCount = Subscription::where(
            'channel_id',
            $user->id
        )->count();

        $totalViews = $user->videos()->sum('views');

        $isSubscribed = false;

        if (auth()->check()) {

            $isSubscribed = Subscription::where('subscriber_id', auth()->id())
                ->where('channel_id', $user->id)
                ->exists();
        }

        return view('channels.show', [
            'channel' => $user,
            'videos' => $videos,
            'subscribersCount' => $subscribersCount,
            'totalViews' => $totalViews,
            'isSubscribed' => $isSubscribed,
        ]);
    }
}