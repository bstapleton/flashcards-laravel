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

    public function getColorClasses(): string
    {
        return match ($this->colour) {
            TagColour::ORANGE => 'border-orange-500 text-orange-700 bg-orange-50 ring-orange-500',
            TagColour::YELLOW => 'border-yellow-500 text-yellow-700 bg-yellow-50 ring-yellow-500',
            TagColour::GREEN => 'border-green-500 text-green-700 bg-green-50 ring-green-500',
            TagColour::TEAL => 'border-teal-500 text-teal-700 bg-teal-50 ring-teal-500',
            TagColour::CYAN => 'border-cyan-500 text-cyan-700 bg-cyan-50 ring-cyan-500',
            TagColour::BLUE => 'border-blue-500 text-blue-700 bg-blue-50 ring-blue-500',
            TagColour::INDIGO => 'border-indigo-500 text-indigo-700 bg-indigo-50 ring-indigo-500',
            TagColour::PURPLE => 'border-purple-500 text-purple-700 bg-purple-50 ring-purple-500',
            TagColour::PINK => 'border-pink-500 text-pink-700 bg-pink-50 ring-pink-500',
            TagColour::RED => 'border-red-500 text-red-700 bg-red-50 ring-red-500',
        };
    }
}
