<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\UserCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
 * @property Collection roles
 * @property bool is_trial_expired
 *
 * @OA\Schema(
 *     required={"username", "password", "display_name"},
 *     @OA\Property(property="username", type="string"),
 *     @OA\Property(property="display_name", type="string"),
 *     @OA\Property(property="password", type="password"),
 *     @OA\Property(property="points", type="number"),
 *     @OA\Property(property="easy_time", type="number"),
 *     @OA\Property(property="medium_time", type="number"),
 *     @OA\Property(property="page_limit", type="number")
 * )
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
        'lose_points',
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
            'is_trial_expired' => 'boolean',
        ];
    }

    protected $dispatchesEvents = [
        'created' => UserCreated::class,
    ];

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withPivot(['valid_until', 'auto_renew']);
    }

    public function getIsTrialExpiredAttribute(): bool
    {
        // Already upgraded? Not a trial to be expired
        if ($this->roles()->where('code', 'advanced_user')->exists()) {
            return false;
        }

        // Older than the trial allows for
        if (now()->subMonth()->greaterThan(Carbon::parse($this->created_at))) {
            return true;
        }

        return false;
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
