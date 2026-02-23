<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'avatar',
        'is_admin',
        'status',
        'suspended_until',
    ];

    protected $casts = [
        'suspended_until' => 'datetime',
    ];

    protected $appends = ['avatar_url'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    public function chirps(): HasMany
    {
        return $this->hasMany(Chirp::class);
    }

    /**
     * Chirps liked by this user
     */
    public function likedChirps(): BelongsToMany
    {
        return $this->belongsToMany(Chirp::class, 'chirp_user_likes')->withTimestamps();
    }

    /**
     * Get user's profile URL
     */
    public function profileUrl(): string
    {
        return route('profile.show', $this);
    }

    /**
     * Get Avatar URL - either uploaded file or Gravatar fallback
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/'.$this->avatar);
        }

        // Fallback to Laravel Cloud avatar service
        return 'https://avatars.laravel.cloud/'.urlencode($this->email);
    }

    // Add relationships
    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function adminLogs()
    {
        return $this->hasMany(AdminLog::class, 'admin_id');
    }
}
