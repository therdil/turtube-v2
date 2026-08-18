<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VideoResource;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/** Lists every video owned by the authenticated creator, including private and processing media. */
class StudioVideoController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return VideoResource::collection(
            Video::query()
                ->where('user_id', $request->user('sanctum')->id)
                ->with(['user', 'category'])
                ->withCount(['likes', 'comments'])
                ->latest()
                ->paginate($validated['per_page'] ?? 30)
                ->withQueryString()
        );
    }
}
