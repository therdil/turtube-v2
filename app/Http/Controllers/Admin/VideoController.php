<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\VideoDeletionService;
use App\Services\ContentCache;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function __construct(
        private VideoDeletionService $videoDeletionService,
        private AdminActivityLogger $activityLogger,
    )
    {
    }

    public function index(Request $request): View
    {
        $videos = Video::query()
            ->with(['user', 'category'])
            ->when($request->filled('q'), fn ($query) => $query->where('title', 'like', '%'.$request->string('q').'%'))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.videos', compact('videos'));
    }

    public function updateStatus(Request $request, Video $video): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:public,private,unlisted,draft']]);
        $video->update($validated);
        ContentCache::flush();
        $this->activityLogger->record($request->user(), 'video.status_updated', 'Video gorunurluk durumu guncellendi.', $video, $validated);

        return back()->with('success', 'Video durumu güncellendi.');
    }

    public function updateModeration(Request $request, Video $video): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:public,private,unlisted,draft'],
            'is_featured' => ['nullable', 'boolean'],
            'age_restriction' => ['required', 'in:0,13,16,18'],
            'copyright_status' => ['required', 'in:none,warning,blocked'],
            'copyright_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        if ($validated['copyright_status'] === 'blocked') {
            $validated['status'] = 'private';
        }

        $video->update($validated);
        ContentCache::flush();
        $this->activityLogger->record($request->user(), 'video.moderated', 'Video moderasyon ayarlari guncellendi.', $video, $validated);

        return back()->with('success', 'Video moderasyon ayarları güncellendi.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'video_ids' => ['required', 'array', 'min:1', 'max:100'],
            'video_ids.*' => ['integer', 'distinct', 'exists:videos,id'],
            'action' => ['required', 'in:delete,feature,unfeature,age_0,age_13,age_16,age_18,copyright_warning,copyright_clear,copyright_block'],
        ]);

        $videos = Video::whereIn('id', $validated['video_ids'])->get();
        if ($validated['action'] === 'delete') {
            $videos->each(fn (Video $video) => $this->videoDeletionService->delete($video));
            ContentCache::flush();
            $this->activityLogger->record($request->user(), 'video.bulk_deleted', 'Toplu video silme islemi uygulandi.', null, ['count' => $videos->count(), 'video_ids' => $videos->pluck('id')->all()]);

            return back()->with('success', $videos->count().' video kalıcı olarak silindi.');
        }

        $updates = match ($validated['action']) {
            'feature' => ['is_featured' => true],
            'unfeature' => ['is_featured' => false],
            'age_0' => ['age_restriction' => 0],
            'age_13' => ['age_restriction' => 13],
            'age_16' => ['age_restriction' => 16],
            'age_18' => ['age_restriction' => 18],
            'copyright_warning' => ['copyright_status' => 'warning'],
            'copyright_clear' => ['copyright_status' => 'none', 'copyright_note' => null],
            'copyright_block' => ['copyright_status' => 'blocked', 'status' => 'private'],
        };

        Video::whereIn('id', $videos->pluck('id'))->update($updates);
        ContentCache::flush();
        $this->activityLogger->record($request->user(), 'video.bulk_updated', 'Toplu video moderasyon islemi uygulandi.', null, ['action' => $validated['action'], 'count' => $videos->count(), 'video_ids' => $videos->pluck('id')->all()]);

        return back()->with('success', $videos->count().' video güncellendi.');
    }
}
