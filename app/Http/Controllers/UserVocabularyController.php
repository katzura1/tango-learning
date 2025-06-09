<?php
namespace App\Http\Controllers;

use App\Models\UserVocabulary;
use App\Models\Vocabulary;
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
}
