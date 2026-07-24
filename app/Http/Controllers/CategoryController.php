<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $videos = $category
            ->videos()
            ->with([
                'user',
                'category',
            ])
            ->latest()
            ->paginate(16);

        return view('categories.show', [
            'category' => $category,
            'videos' => $videos,
        ]);
    }
}