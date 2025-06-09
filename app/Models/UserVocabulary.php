<?php
namespace App\Models;

use App\Models\User;
use App\Models\Vocabulary;
use Illuminate\Database\Eloquent\Model;

class UserVocabulary extends Model
{
    protected $fillable = [
        'user_id',
        'vocabulary_id',
        'status',
        'favorite',
        'last_reviewed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vocabulary()
    {
        return $this->belongsTo(Vocabulary::class);
    }
}
