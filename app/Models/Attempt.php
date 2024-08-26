<?php

namespace App\Models;

use App\Enums\Correctness;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string answered_at
 * @property Correctness correctness
 */
class Attempt extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'flashcard_id',
        'answered_at',
        'correctness'
    ];

    protected function casts(): array
    {
        return [
            'correctness' => Correctness::class,
        ];
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }
}
