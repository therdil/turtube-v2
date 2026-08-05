<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\View\View;

class SubscriptionFeedController extends Controller
{
    /**
     * Abone olunan kanalların en yeni videoları.
     */
    public function index(): View
    {
        $channelIds = auth()->user()
            ->subscribedChannels()
            ->select('users.id');

        $videos = Video::query()
            ->published()
            ->whereIn('user_id', $channelIds)
            ->with(['user', 'category'])
            ->latest()
            ->paginate(16);

        return view('subscriptions-index', compact('videos'));
    }
}
