<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\Chirp;
use App\Models\Report;
use Illuminate\Http\Request;

class ChirpModerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Chirp::with(['user', 'likes'])->withCount('likes');

        // Filter by report status
        if ($request->input('filter') === 'reported') {
            $query->whereHas('reports', function ($q) {
                $q->where('status', 'pending');
            });
        }

        // Search chirps
        if ($search = $request->input('search')) {
            $query->where('message', 'like', "%{$search}%");
        }

        // Filter by user status
        if ($request->input('user_status')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('status', $request->input('user_status'));
            });
        }

        $chirps = $query->latest()->paginate(20)->withQueryString();

        return view('admin.chirps.index', compact('chirps'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        $chirp->load(['user', 'likes', 'reports' => function ($q) {
            $q->with('reporter')->where('status', 'pending');
        }]);

        $reports = Report::where('reportable_type', Chirp::class)
            ->where('reportable_id', $chirp->id)
            ->with(['reporter', 'resolver'])
            ->latest()
            ->get();

        return view('admin.chirps.show', compact('chirp', 'reports'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Chirp $chirp)
    {
        $request->validate([
            'reason' => 'required|string|min:10',
        ]);

        // Log before deletion
        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'delete_chirp',
            'target_type' => Chirp::class,
            'target_id' => $chirp->id,
            'reason' => $request->reason,
            'metadata' => [
                'message' => $chirp->message,
                'user_id' => $chirp->user_id,
                'likes_count' => $chirp->likes()->count(),
            ],
        ]);

        // Resolve all pending reports
        Report::where('reportable_type', Chirp::class)
            ->where('reportable_id', $chirp->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'resolved',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'resolution_note' => 'Content removed by moderator: '.$request->reason,
            ]);
        $chirp->delete();

        return redirect()->route('admin.chirps.index')->with('success', 'Chirp removed and reports resolved.');
    }

    public function dismissReports(Request $request, Chirp $chirp)
    {
        $request->validate([
            'resolution_note' => 'required|string|min:5',
        ]);

        Report::where('reportable_type', Chirp::class)
            ->where('reportable_id', $chirp->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'dismissed',
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
                'resolution_note' => $request->resolution_note,
            ]);

        return redirect()->route('admin.chirps.show', $chirp)->with('success', 'Reports dismissed.');
    }
}
