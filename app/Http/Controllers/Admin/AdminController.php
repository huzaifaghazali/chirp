<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chirp;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Analytics calculations
        $stats = [
            'total_users' => User::count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_chirps' => Chirp::count(),
            'chirps_today' => Chirp::whereDate('created_at', today())->count(),
            'chirps_this_week' => Chirp::where('created_at', '>=', now()->subDays(7))->count(),
            'total_likes' => DB::table('chirp_user_likes')->count(),
            'likes_today' => DB::table('chirp_user_likes')->whereDate('created_at', today())->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_reports' => Report::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
        ];

        // Chart data - User registration over last 30 days
        $userChartData = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Chart data - Chirps over last 30 days
        $chirpChartData = Chirp::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Recent activity
        $recentUsers = User::latest()->take(5)->get();
        $recentChirps = Chirp::with('user')->latest()->take(5)->get();
        $recentReports = Report::with(['reportable', 'reporter'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Top engaged users
        $topUsers = User::withCount(['chirps', 'likedChirps'])
            ->orderByDesc('chirps_count')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'userChartData',
            'chirpChartData',
            'recentUsers',
            'recentChirps',
            'recentReports',
            'topUsers',
        ));
    }
}
