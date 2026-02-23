<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Role filter
        if ($request->input('role') === 'admin') {
            $query->where('is_admin', true);
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'most_chirps' => $query->withCount('chirps')->orderByDesc('chirps_count'),
            'most_likes_given' => $query->withCount('likedChirps')->orderByDesc('liked_chirps_count'),
            default => $query->latest(),
        };

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['chirps' => function ($q) {
            $q->latest()->take(10);
        }, 'chirps.likes']);

        $stats = [
            'total_chirps' => $user->chirps()->count(),
            'total_likes_received' => $user->chirps()->withCount('likes')->get()->sum('likes_count'),
            'total_likes_given' => $user->likedChirps()->count(),
            'reports_against' => \App\Models\Report::where('reportable_type', User::class)
                ->where('reportable_id', $user->id)
                ->count(),
            'reports_filed' => $user->reports()->count(),
        ];

        $adminLogs = AdminLog::where('target_type', User::class)
            ->where('target_id', $user->id)
            ->with('admin')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'stats', 'adminLogs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'bio' => 'nullable|string|max:500',
            'is_admin' => 'boolean',
            'status' => 'in:active,suspended,banned',
        ]);

        $oldStatus = $user->status;
        $oldAdmin = $user->is_admin;

        $user->update($validated);

        // Log status change
        if ($oldStatus !== $user->status) {
            AdminLog::create([
                'admin_id' => auth()->id(),
                'action' => "status_change_{$user->status}",
                'target_type' => User::class,
                'target_id' => $user->id,
                'reason' => $request->input('reason', 'Status updated by admin'),
                'metadata' => ['previous_status' => $oldStatus, 'new_status' => $user->status],
            ]);
        }

        // Log admin privilege change
        if ($oldAdmin !== $user->is_admin) {
            AdminLog::create([
                'admin_id' => auth()->id(),
                'action' => $user->is_admin ? 'granted_admin' : 'revoked_admin',
                'target_type' => User::class,
                'target_id' => $user->id,
                'reason' => $request->input('reason'),
            ]);
        }

        return redirect()->route('admin.users.show', $user)->with('success', 'User updated successfully.');
    }

    public function suspend(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|min:10',
            'duration' => 'required|in:1_day,3_days,7_days,30_days,permanent',
        ]);

        if ($user->is_admin && $user->id !== auth()->id()) {
            return back()->with('error', 'Cannot suspend other admins.');
        }

        $suspendedUntil = match ($request->duration) {
            '1_day' => now()->addDay(),
            '3_days' => now()->addDays(3),
            '7_days' => now()->addWeek(),
            '30_days' => now()->addDays(30),
            'permanent' => null,
        };

        $user->update([
            'status' => 'suspended',
            'suspended_until' => $suspendedUntil,
        ]);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'suspend_user',
            'target_type' => User::class,
            'target_id' => $user->id,
            'reason' => $request->reason,
            'metadata' => [
                'duration' => $request->duration,
                'suspended_until' => $suspendedUntil,
            ],
        ]);

        //    return redirect()->route('admin.users.show', $user)->with('success', "User suspended until {$suspendedUntil?->format('M d, Y') ?? 'permanently'}");

        $dateString = $suspendedUntil?->format('M d, Y') ?? 'permanently';

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User suspended until {$dateString}");
    }

    public function ban(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        if ($user->is_admin) {
            return back()->with('error', 'Cannot ban admin users.');
        }

        $user->update([
            'status' => 'banned',
            'suspended_until' => null,
        ]);

        // Revoke all sessions
        DB::table('sessions')->where('user_id', $user->id)->delete();

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'ban_user',
            'target_type' => User::class,
            'target_id' => $user->id,
            'reason' => $request->reason,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User has been banned.');
    }

    public function activate(User $user)
    {
        $user->update([
            'status' => 'active',
            'suspended_until' => null,
        ]);

        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'activate_user',
            'target_type' => User::class,
            'target_id' => $user->id,
            'reason' => 'User account reactivated',
        ]);

        return redirect()->route('admin.users.show', $user)->with('success', 'User activated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|min:10',
            'confirm_email' => 'required|in:'.$user->email,
        ]);

        if ($user->is_admin) {
            return back()->with('error', 'Cannot delete admin users.');
        }

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Log before deletion
        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'delete_user',
            'target_type' => User::class,
            'target_id' => $user->id,
            'reason' => $request->reason,
            'metadata' => ['user_email' => $user->email, 'chirps_count' => $user->chirps()->count()],
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User and all associated data deleted.');
    }
}
