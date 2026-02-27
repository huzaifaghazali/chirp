<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Toggle like on a chirp (AJAX)
     */
    public function toggle(Request $request, Chirp $chirp): JsonResponse
    {
        $user = $request->user();

        // Check rate limit manually for immediate feedback
        $key = "likes:" . $user->id . ":" . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 30)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => __("ratelimit.likes", [
                    'time' => $this->secondsToHuman($seconds),
                    'seconds' => $seconds,
                ]),
                'retry_after' => $seconds,
                'rate_limited' => true,
            ], 429);
        }

        $isLiked = $chirp->toggleLike($user);
        $likesCount = $chirp->likes()->count();

        // Add rate limit info to successful response
        $remaining = 30 - RateLimiter::attempts($key);

        return response()->json([
            'success' => true,
            'liked' => $isLiked,
            'likes_count' => $likesCount,
            'message' => $isLiked ? 'Chirp liked!' : 'Chirp unliked!',
             'rate_limit' => [
                'remaining' => max(0, $remaining),
                'limit' => 30,
            ],
        ]);
    }

    /**
     * Get likes info for a chirp (AJAX)
     */
    public function show(Chirp $chirp): JsonResponse
    {
        return response()->json([
            'likes_count' => $chirp->likes()->count(),
            'is_liked_by_current_user' => auth()->check() ? $chirp->isLikedBy(auth()->user()) : false,
        ]);
    }


      /**
     * Convert seconds to human-readable format.
     */
    protected function secondsToHuman(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds !== 1 ? 's' : '');
        }
        
        $minutes = ceil($seconds / 60);
        
        if ($minutes < 60) {
            return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
        }
        
        $hours = ceil($minutes / 60);
        return $hours . ' hour' . ($hours !== 1 ? 's' : '');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Chirp $chirp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Chirp $chirp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Chirp $chirp)
    {
        //
    }
}
