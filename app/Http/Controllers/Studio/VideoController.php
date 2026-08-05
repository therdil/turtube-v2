<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use App\Services\CreatorScoreService;
use App\Services\VideoDeletionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoController extends Controller
{
    public function __construct(
        protected CreatorScoreService $creatorScore,
        protected VideoDeletionService $videoDeletionService
    ) {
    }

    public function index(Request $request)
    {
        $videos = $this->filteredVideos($request)
            ->paginate(15)
            ->withQueryString();

        $videos->getCollection()->transform(function (Video $video) {
            $video->creator_score = $this->creatorScore->score($video);
            $video->creator_badge = $this->creatorScore->badge($video);
            $video->creator_suggestions = $this->creatorScore->suggestions($video);

            return $video;
        });

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);
        $playlists = Auth::user()->playlists()->orderBy('name')->get(['id', 'name']);

        return view('studio.videos.index', compact('videos', 'categories', 'playlists'));
    }

    public function export(Request $request): StreamedResponse
    {
        $videos = $this->filteredVideos($request)->get();

        return response()->streamDownload(function () use ($videos): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Başlık', 'Durum', 'Tür', 'Kategori', 'Görüntülenme', 'Beğeni', 'Yorum', 'Creator Score', 'Performans', 'Yayın tarihi']);

            foreach ($videos as $video) {
                fputcsv($output, [
                    $video->title,
                    $video->status,
                    $video->is_short ? 'Shorts' : 'Standart video',
                    $video->category?->name,
                    $video->views,
                    $video->likes_count,
                    $video->comments_count,
                    $this->creatorScore->score($video),
                    $this->creatorScore->badge($video)['text'],
                    $video->created_at->toDateTimeString(),
                ]);
            }

            fclose($output);
        }, 'turtube-videolar-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'video_ids' => ['required', 'array', 'min:1'],
            'video_ids.*' => ['integer', Rule::exists('videos', 'id')],
            'action' => ['required', Rule::in(['status', 'category', 'playlist', 'delete'])],
            'status' => [Rule::requiredIf($request->input('action') === 'status'), Rule::in(['public', 'private', 'unlisted', 'draft'])],
            'category_id' => [Rule::requiredIf($request->input('action') === 'category'), 'nullable', 'integer', Rule::exists('categories', 'id')],
            'playlist_id' => [Rule::requiredIf($request->input('action') === 'playlist'), 'nullable', 'integer', Rule::exists('playlists', 'id')],
        ]);

        $videos = Video::query()
            ->where('user_id', Auth::id())
            ->whereKey($validated['video_ids'])
            ->get();

        abort_if(
            $videos->count() !== collect($validated['video_ids'])->unique()->count(),
            403,
            'Yalnızca kendi videolarınız üzerinde toplu işlem yapabilirsiniz.'
        );

        if ($validated['action'] === 'delete') {
            foreach ($videos as $video) {
                $this->videoDeletionService->delete($video);
            }

            return back()->with('success', $videos->count().' video ve ilgili dosyalar silindi.');
        }

        if ($validated['action'] === 'category') {
            $videos->each->update([
                'category_id' => $validated['category_id'],
                'updated_at' => now(),
            ]);

            return back()->with('success', $videos->count().' video için kategori güncellendi.');
        }

        if ($validated['action'] === 'playlist') {
            $playlist = Auth::user()->playlists()->findOrFail($validated['playlist_id']);
            $playlist->videos()->syncWithoutDetaching($videos->modelKeys());

            return back()->with('success', $videos->count().' video oynatma listesine eklendi.');
        }

        $updated = $videos->each->update([
                'status' => $validated['status'],
                'updated_at' => now(),
            ])->count();

        return back()->with('success', $updated.' video için görünürlük durumu güncellendi.');
    }

    private function filteredVideos(Request $request): Builder
    {
        $query = Video::query()
            ->with('category')
            ->withCount(['likes', 'comments'])
            ->where('user_id', Auth::id());

        /*
        |--------------------------------------------------------------------------
        | Arama
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {

                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Durum
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('status') &&
            $request->status !== 'all'
        ) {

            $query->where('status', $request->status);

        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if (in_array($request->input('processing'), ['pending', 'processing', 'ready', 'failed'], true)) {
            $query->where('processing_status', $request->input('processing'));
        }

        if ($request->input('premium') === 'yes') {
            $query->where('is_premium', true);
        }

        if ($request->input('premium') === 'no') {
            $query->where('is_premium', false);
        }

        if ($request->input('thumbnail') === 'yes') {
            $query->whereNotNull('thumbnail');
        }

        if ($request->input('thumbnail') === 'no') {
            $query->whereNull('thumbnail');
        }

        if ($request->input('type') === 'short') {
            $query->where('is_short', true);
        }

        if ($request->input('type') === 'video') {
            $query->where('is_short', false);
        }

        $periodDays = [
            '7' => 7,
            '30' => 30,
            '90' => 90,
        ];

        if (isset($periodDays[$request->input('period')])) {
            $query->where('created_at', '>=', now()->subDays($periodDays[$request->input('period')]));
        }

        /*
        |--------------------------------------------------------------------------
        | Sıralama
        |--------------------------------------------------------------------------
        */

        switch ($request->sort) {

            case 'views':

                $query->orderByDesc('views');

                break;

            case 'likes':

                $query->orderByDesc('likes_count');

                break;

            case 'comments':

                $query->orderByDesc('comments_count');

                break;

            case 'oldest':

                $query->oldest();

                break;

            default:

                $query->latest();

        }

        return $query;
    }
}
