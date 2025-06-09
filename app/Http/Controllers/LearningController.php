<?php
namespace App\Http\Controllers;

use App\Models\Category;

class LearningController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('vocabularies')
            ->orderBy('name')
            ->get();

        return inertia('learning/Index', [
            'categories' => $categories,
        ]);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->with('vocabularies')
            ->firstOrFail();

        return inertia('learning/Show', [
            'category'     => $category,
            'vocabularies' => $category->vocabularies,
        ]);
    }
}
