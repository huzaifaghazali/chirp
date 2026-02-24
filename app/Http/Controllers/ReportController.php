<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
  
    /**
     * Store a new report from authenticated user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:chirp,user',
            'id' => 'required|integer',
            'reason' => 'required|in:spam,harassment,misinformation,hate_speech,violence,other',
            'details' => 'nullable|string|max:500',
        ]);

        // Determine the model being reported
        $reportable = match($validated['type']) {
            'chirp' => Chirp::find($validated['id']),
            'user' => User::find($validated['id']),
        };

        if (!$reportable) {
            return back()->with('error', 'The content you are trying to report no longer exists.');
        }

        // Prevent self-reporting
        if ($validated['type'] === 'user' && $reportable->id === auth()->id()) {
            return back()->with('error', 'You cannot report yourself.');
        }

        if ($validated['type'] === 'chirp' && $reportable->user_id === auth()->id()) {
            return back()->with('error', 'You cannot report your own chirp.');
        }

        // Check for existing pending report
        $existingReport = Report::where('reportable_type', get_class($reportable))
            ->where('reportable_id', $reportable->id)
            ->where('reporter_id', auth()->id())
            ->whereIn('status', ['pending', 'under_review'])
            ->first();

        if ($existingReport) {
            return back()->with('error', 'You have already reported this content. Our moderators are reviewing it.');
        }

        // Create the report
        Report::create([
            'reportable_type' => get_class($reportable),
            'reportable_id' => $reportable->id,
            'reporter_id' => auth()->id(),
            'reason' => $validated['reason'],
            'details' => $validated['details'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Thank you for your report. Our moderators will review it shortly.');
    }

    /**
     * Show user's submitted reports
     */
    public function myReports()
    {
        $reports = Report::where('reporter_id', auth()->id())
            ->with(['reportable', 'resolver'])
            ->latest()
            ->paginate(20);

        return view('reports.my-reports', compact('reports'));
    }
}
