<?php
namespace App\Models;

use App\Models\Category;
use App\Models\User;
use App\Models\UserVocabulary;
use Illuminate\Database\Eloquent\Model;

class Vocabulary extends Model
{
    protected $fillable = [
        'category_id',
        'hiragana',
        'meaning',
        'romaji',
        'example',
        'user_id',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVocabularies()
    {
        return $this->hasMany(UserVocabulary::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_vocabularies')
            ->withPivot('status', 'favorite', 'last_reviewed_at')
            ->withTimestamps();
    }
}
