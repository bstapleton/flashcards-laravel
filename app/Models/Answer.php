<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property integer id
 * @property string text
 * @property string explanation
 * @property boolean is_correct
 *
 * @OA\Schema(
 *     required={"text"},
 *     @OA\Property(property="flashcard_id", type="integer", example=1),
 *     @OA\Property(property="text", type="string", example="Blue"),
 *     @OA\Property(property="explanation", type="string", example="Blue is the colour of the sky"),
 *     @OA\Property(property="is_correct", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Initial creation timestamp", readOnly="true"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp", readOnly="true")
 * )
 */
class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'flashcard_id',
        'text',
        'explanation',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }
}
