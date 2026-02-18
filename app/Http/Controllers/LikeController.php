<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $isLiked = $chirp->toggleLike($user);
        $likesCount = $chirp->likes()->count();

        return response()->json([
            'success' => true,
            'liked' => $isLiked,
            'likes_count' => $likesCount,
            'message' => $isLiked ? 'Chirp liked!' : 'Chirp unliked!',
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
