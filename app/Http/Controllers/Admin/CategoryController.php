<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\ContentCache;
use App\Services\AdminActivityLogger;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private AdminActivityLogger $activityLogger)
    {
    }

    public function index(): View
    {
        $categories = Category::query()
            ->withCount('videos')
            ->orderBy('name')
            ->get();

        return view('admin.categories', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
        ]);

        Cache::forget('navigation-categories-v3');
        ContentCache::flush();
        $this->activityLogger->record($request->user(), 'category.created', 'Kategori olusturuldu.', $category);

        return back()->with('success', 'Kategori oluşturuldu.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,'.$category->id],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $category->id),
        ]);

        Cache::forget('navigation-categories-v3');
        ContentCache::flush();
        $this->activityLogger->record($request->user(), 'category.updated', 'Kategori guncellendi.', $category);

        return back()->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->videos()->exists()) {
            return back()->with('error', 'Video içeren bir kategori silinemez. Önce videoları başka kategoriye taşıyın.');
        }

        $category->delete();
        Cache::forget('navigation-categories-v3');
        ContentCache::flush();
        $this->activityLogger->record(auth()->user(), 'category.deleted', 'Kategori silindi.', null, ['category_id' => $category->id, 'name' => $category->name]);

        return back()->with('success', 'Kategori silindi.');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $suffix = 2;

        while (Category::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
