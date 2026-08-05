<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ContentCache;

class ChannelController extends Controller
{
    public function index()
    {
        return view('studio.channel.index', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([

            'channel_name' => [
                'required',
                'string',
                'max:100',
            ],

            'channel_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'avatar' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'banner' => [
                'nullable',
                'image',
                'max:4096',
            ],

            'website' => ['nullable', 'url', 'max:2048'],
            'instagram' => ['nullable', 'url', 'max:2048'],
            'x' => ['nullable', 'url', 'max:2048'],
            'facebook' => ['nullable', 'url', 'max:2048'],
            'youtube' => ['nullable', 'url', 'max:2048'],
            'channel_tags_text' => ['nullable', 'string', 'max:500'],
            'seo_keywords_text' => ['nullable', 'string', 'max:1000'],
            'channel_language' => ['required', 'in:tr,en,de,fr,es,ar'],
            'default_video_status' => ['required', 'in:public,private,unlisted,draft'],
            'default_video_description' => ['nullable', 'string', 'max:5000'],
            'default_video_license' => ['required', 'in:standard,creative_commons'],

        ]);

        $user = Auth::user();

        $validated['social_links'] = collect([
            'website' => $validated['website'] ?? null,
            'instagram' => $validated['instagram'] ?? null,
            'x' => $validated['x'] ?? null,
            'facebook' => $validated['facebook'] ?? null,
            'youtube' => $validated['youtube'] ?? null,
        ])->filter()->all() ?: null;

        unset($validated['website'], $validated['instagram'], $validated['x'], $validated['facebook'], $validated['youtube']);

        $validated['channel_tags'] = $this->keywords($validated['channel_tags_text'] ?? null, 15);
        $validated['seo_keywords'] = $this->keywords($validated['seo_keywords_text'] ?? null, 30);
        unset($validated['channel_tags_text'], $validated['seo_keywords_text']);

        if ($request->hasFile('avatar')) {

            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $validated['avatar'] = $request
                ->file('avatar')
                ->store('avatars', 'public');
        }

        if ($request->hasFile('banner')) {

            if ($user->banner) {
                Storage::disk('public')->delete($user->banner);
            }

            $validated['banner'] = $request
                ->file('banner')
                ->store('banners', 'public');
        }

        $user->update($validated);
        ContentCache::flush();

        return back()->with(
            'success',
            'Kanal bilgileri güncellendi.'
        );
    }

    private function keywords(?string $value, int $limit): ?array
    {
        $items = collect(explode(',', (string) $value))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->unique(fn ($item) => mb_strtolower($item))
            ->take($limit)
            ->values()
            ->all();

        return $items ?: null;
    }
}
