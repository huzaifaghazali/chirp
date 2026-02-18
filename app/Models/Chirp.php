<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Chirp extends Model
{
    protected $fillable = [
        'message',
    ];

    protected $withCount = ['likes']; // Auto-load likes count

    protected $appends = ['is_liked_by_current_user']; // Add to JSON/array output

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Users who liked this chirp
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chirp_user_likes')->withTimestamps();
    }
 
    /**
     * Check if current user liked this chirp
     */
    public function getIsLikedByCurrentUserAttribute(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        
        return $this->likes()->where('user_id', auth()->id())->exists();
    }

    /**
     * Like this chirp
     */
    public function like(User $user): void 
    {
        $this->likes()->syncWithoutDetaching($user->id);
    }

    /**
     * Unlike this chirp
     */
    public function unlike(User $user): void 
    {
        $this->likes()->detach($user->id);
    }

    /**
     * Toggle like status
     */

    public function toggleLike(User $user): bool
    {
        if ($this->isLikedBy($user)) {
            $this->unlike($user);
            return false;
        }

        $this->like($user);
        return true;
    }

    
    /**
     * Check if specific user liked this chirp
     */

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}

// Auth::check()