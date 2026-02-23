<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Report::with(['reportable', 'reporter', 'resolver']);

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Reason filter
        if ($reason = $request->input('reason')) {
            $query->where('reason', $reason);
        }

        // Type filter
        if ($type = $request->input('type')) {
            $query->where('reportable_type', $type === 'user' ? \App\Models\User::class : \App\Models\Chirp::class);
        }

        $reports = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'pending' => Report::where('status', 'pending')->count(),
            'resolved' => Report::where('status', 'resolved')->count(),
            'dismissed' => Report::where('status', 'dismissed')->count(),
            'by_reason' => Report::select('reason', \DB::raw('count(*) as count'))
                ->groupBy('reason')
                ->pluck('count', 'reason'),
        ];

        return view('admin.reports.index', compact('reports', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $report->load(['reportable', 'reporter', 'resolver']);

        return view('admin.reports.show', compact('report'));
    }

    public function resolve(Request $request, Report $report)
    {
        $request->validate([
            'action' => 'required|in:content_removed,warning_issued,dismissed',
            'resolution_note' => 'required|string|min:5',
        ]);

        $report->update([
            'status' => $request->action === 'dismissed' ? 'dismissed' : 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'resolution_note' => $request->resolution_note,
        ]);

        // If content removal requested, handle it
        if ($request->action === 'content_removed' && $report->reportable) {
            $report->reportable->delete();
        }

        return redirect()->route('admin.reports.index')->with('success', 'Report resolved.');
    }
}
