<?php

namespace App\Http\Controllers;

use App\Models\Category;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $videos = $this->query($category)->paginate(16);

        return view('categories.show', compact('category', 'videos'));
    }

    private function query(Category $category)
    {
        return $category->videos()->published()->with(['user', 'category'])->latest();
    }
}
