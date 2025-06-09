<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserVocabulary;
use Illuminate\Support\Facades\Auth;

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
        $user     = Auth::user();
        $category = Category::where('slug', $slug)
            ->with('vocabularies')
            ->firstOrFail();

        // Get user's vocabulary preferences
        $userVocabs = UserVocabulary::where('user_id', $user->id)
            ->whereIn('vocabulary_id', $category->vocabularies->pluck('id'))
            ->get()
            ->keyBy('vocabulary_id');

        $vocabularies = $category->vocabularies->map(function ($vocabulary) use ($userVocabs) {
            $userVocab                    = $userVocabs->get($vocabulary->id);
            $vocabulary->is_favorited     = $userVocab ? $userVocab->favorite === 'yes' : false;
            $vocabulary->status           = $userVocab ? $userVocab->status : 'learning';
            $vocabulary->last_reviewed_at = $userVocab ? $userVocab->last_reviewed_at : null;
            return $vocabulary;
        });

        return inertia('learning/Show', [
            'category'     => $category,
            'vocabularies' => $vocabularies,
        ]);
    }
}
