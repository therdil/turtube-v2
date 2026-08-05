<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ContentCache;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $page = max(1, (int) request('page', 1));
        $videos = auth()->guest()
            ? ContentCache::remember('category', $category->id.':page:'.$page, 180, fn () => $this->query($category)->paginate(16))
            : $this->query($category)->paginate(16);

        return view('categories.show', compact('category', 'videos'));
    }

    private function query(Category $category)
    {
        return $category->videos()->published()->with(['user', 'category'])->latest();
    }
}
