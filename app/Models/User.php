<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use RedExplosion\Sqids\Concerns\HasSqids;

/**
 * @property int id
 * @property string username
 * @property string display_name
 * @property string password
 * @property int points
 * @property string created_at
 * @property int easy_time
 * @property int medium_time
 * @property int hard_time
 * @property int page_limit
 * @property bool lose_points
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasSqids;

    protected string $sqidPrefix = 'u';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'display_name',
        'password',
        'points',
        'easy_time',
        'medium_time',
        'hard_time',
        'page_limit',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lose_points' => 'boolean',
        ];
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function adjustPoints(int $score, $operation = 'add'): static
    {
        if ('add' === $operation) {
            $this->points += $score;
        } else {
            $this->points = $this->points - $score;
        }

        $this->save();

        return $this;
    }
}
