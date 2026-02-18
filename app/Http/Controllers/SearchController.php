<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle search requests.
     */
    public function __invoke(Request $request)
    {
        $query = $request->input('q');
        $filter = $request->input('filter', 'all');  // All users, chirps

        // Require at least 2 characters to search
        if (!$query || strlen($query) < 2) {
            return view('search.results', [
                'query' => $query,
                'filter' => $filter,
                'users' => collect(),
                'chirps' => collect(),
                'message' => 'Please enter at least 2 characters to search.',
            ]);
        }

        $users = collect();
        $chirps = collect();

        // Search Users (name or email)
        if (in_array($filter, ['all', 'users'])) {
            $users = User::query()
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('bio', 'like', "%{$query}%")
                ->limit(20)
                ->get();
        }

        // Search Chirps (message content) Paginated
        if (in_array($filter, ['all', 'chirps'])) {
            $chirps = Chirp::query()
                ->with('user')
                ->where('message', 'like', "%{$query}%")
                ->latest()
                ->paginate(10)
                ->appends(['q' => $query, 'filter' => $filter]); // Preserve search params
        }

        return view('search.results', [
            'query' => $query,
            'filter' => $filter,
            'users' => $users,
            'chirps' => $chirps,
            'totalResults' => $users->count() + ($chirps instanceof \Illuminate\Pagination\LengthAwarePaginator ? $chirps->total() : $chirps->count()),
        ]);
    }
}
