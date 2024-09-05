<?php

namespace App\Models;

use App\Enums\TagColour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string name
 * @property TagColour colour
 */
class Tag extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'colour',
    ];

    protected $casts = [
        'colour' => TagColour::class,
    ];

    public function flashcards(): BelongsToMany
    {
        return $this->belongsToMany(Flashcard::class, 'flashcard_tag')
            ->distinct();
    }
}
