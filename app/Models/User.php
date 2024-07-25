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
 * @property string name
 * @property string email
 * @property string password
 * @property int points
 * @property string created_at
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
        'name',
        'email',
        'password',
        'points',
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
        ];
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }
}
