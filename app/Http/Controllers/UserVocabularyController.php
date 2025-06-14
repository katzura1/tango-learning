<?php
namespace App\Http\Controllers;

use App\Models\UserVocabulary;
use App\Models\Vocabulary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserVocabularyController extends Controller
{
    /**
     * Toggle favorite status
     */
    public function toggleFavorite($vocabularyId)
    {
        $user = Auth::user();

        // Find existing record or create new one
        $userVocabulary = UserVocabulary::firstOrNew([
            'user_id'       => $user->id,
            'vocabulary_id' => $vocabularyId,
        ]);

        // Toggle favorite status
        $isFavorite               = $userVocabulary->favorite === 'yes';
        $userVocabulary->favorite = $isFavorite ? 'no' : 'yes';

        // Set default values for new records
        if (! $userVocabulary->exists) {
            $userVocabulary->status = 'learning';
        }

        $userVocabulary->save();

        return response()->json([
            'status'    => 'success',
            'favorited' => $userVocabulary->favorite === 'yes',
            'message'   => $userVocabulary->favorite === 'yes'
            ? 'Added to favorites'
            : 'Removed from favorites',
        ]);
    }

    public function updateStatus($vocabularyId, Request $request)
    {
        $request->validate([
            'status' => 'required|in:learning,familiar,mastered',
        ]);

        $user           = Auth::user();
        $userVocabulary = UserVocabulary::firstOrNew([
            'user_id'       => $user->id,
            'vocabulary_id' => $vocabularyId,
        ]);

        $userVocabulary->status = $request->status;

        if (! $userVocabulary->exists) {
            $userVocabulary->favorite = 'no';
        }

        $userVocabulary->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Status updated successfully',
        ]);
    }

    /**
     * Get user's favorites
     */
    public function favorites()
    {
        $user = Auth::user();

        $favorites = Vocabulary::select('vocabularies.*')
            ->join('user_vocabularies', 'vocabularies.id', '=', 'user_vocabularies.vocabulary_id')
            ->where('user_vocabularies.user_id', $user->id)
            ->where('user_vocabularies.favorite', 'yes')
            ->with('category')
            ->get();

        return inertia('learning/Favorites', [
            'favorites' => $favorites,
        ]);
    }

    public function dataDashboard()
    {
        $user = Auth::user();

        $userVocabulary = UserVocabulary::where('user_id', $user->id)
            ->get();

        $learningCount = $userVocabulary->where('status', 'learning')->count();
        $familiarCount = $userVocabulary->where('status', 'familiar')->count();
        $masteredCount = $userVocabulary->where('status', 'mastered')->count();
        $totalCount    = $userVocabulary->count();

        $learningPercentage = $totalCount > 0 ? ($learningCount / $totalCount) * 100 : 0;
        $familiarPercentage = $totalCount > 0 ? ($familiarCount / $totalCount) * 100 : 0;
        $masteredPercentage = $totalCount > 0 ? ($masteredCount / $totalCount) * 100 : 0;

        return response()->json([
            'learningCount'      => $learningCount,
            'familiarCount'      => $familiarCount,
            'masteredCount'      => $masteredCount,
            'totalCount'         => $totalCount,
            'learningPercentage' => round($learningPercentage, 2),
            'familiarPercentage' => round($familiarPercentage, 2),
            'masteredPercentage' => round($masteredPercentage, 2),
        ]);
    }
}
