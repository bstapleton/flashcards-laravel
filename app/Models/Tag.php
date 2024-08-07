<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string name
 */
class Tag extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name'
    ];

    public function flashcards(): BelongsToMany
    {
        return $this->belongsToMany(Flashcard::class, 'flashcard_tag')
            ->distinct();
    }
}
