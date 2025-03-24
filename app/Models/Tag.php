<?php

namespace App\Models;

use App\Enums\TagColour;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int user_id
 * @property string name
 * @property TagColour colour
 *
 * @OA\Schema(
 *     required={"name", "colour"},
 *
 *     @OA\Property(property="name", type="string", example="Mathematics"),
 *     @OA\Property(property="colour", type="string", example="green")
 * )
 */
class Tag extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
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
