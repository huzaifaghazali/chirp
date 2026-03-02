<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
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

        // Capture reportable data before potential deletion
        $reportable = $report->reportable;
        $reportableType = $report->reportable_type;
        $reportableId = $report->reportable_id;

        // Build metadata for logging
        $metadata = [
            'report_id' => $report->id,
            'report_reason' => $report->reason,
            'resolution_action' => $request->action,
            'resolution_note' => $request->resolution_note,
            'reporter_id' => $report->reporter_id,
        ];

        // If content removal requested, handle it and log BEFORE deletion
        if ($request->action === 'content_removed' && $reportable) {
            // Capture content data before deletion for audit trail
            if ($reportableType === \App\Models\Chirp::class) {
                $metadata['chirp_message'] = $reportable->message;
                $metadata['chirp_user_id'] = $reportable->user_id;
                $metadata['chirp_created_at'] = $reportable->created_at->toDateTimeString();
                $metadata['chirp_likes_count'] = $reportable->likes()->count();

                // Log the content removal BEFORE deleting
                AdminLog::create([
                    'admin_id' => auth()->id(),
                    'action' => 'delete_chirp',
                    'target_type' => $reportableType,
                    'target_id' => $reportableId,
                    'reason' => "Report #{$report->id} resolved with content removal: ".$request->resolution_note,
                    'metadata' => $metadata,
                ]);

                // Now delete the content
                $reportable->delete();
            } elseif ($reportableType === \App\Models\User::class) {
                $metadata['user_name'] = $reportable->name;
                $metadata['user_email'] = $reportable->email;
                $metadata['user_chirps_count'] = $reportable->chirps()->count();

                // Log user-related action (typically wouldn't delete user, but suspend/ban)

                AdminLog::create([
                    'admin_id' => auth()->id(),
                    'action' => 'ban_user',
                    'target_type' => $reportableType,
                    'target_id' => $reportableId,
                    'reason' => "Report #{$report->id} resolved with account action: ".$request->resolution_note,
                    'metadata' => $metadata,
                ]);

                // Apply appropriate action (ban for users rather than delete)
                $reportable->update(['status' => 'banned', 'suspended_until' => null]);

                \DB::table('sessions')->where('user_id', $reportable->id)->delete();
            }
        }

        // Update report status
        $report->update([
            'status' => $request->action === 'dismissed' ? 'dismissed' : 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
            'resolution_note' => $request->resolution_note,
        ]);

        // Log the report resolution itself (for all actions, including dismiss/warning)
        AdminLog::create([
            'admin_id' => auth()->id(),
            'action' => 'resolve_report',
            'target_type' => Report::class,
            'target_id' => $report->id,
            'reason' => $request->resolution_note,
            'metadata' => array_merge($metadata, [
                'final_status' => $report->fresh()->status,
                'content_was_removed' => $request->action === 'content_removed',
            ]),
        ]);

        // Resolve all related pending reports for the same content
        if ($request->action === 'content_removed' && $reportableType) {
            $relatedReports = Report::where('reportable_type', $reportableType)
                ->where('reportable_id', $reportableId)
                ->where('status', 'pending')
                ->where('id', '!=', $report->id)
                ->get();

            foreach ($relatedReports as $relatedReport) {
                $relatedReport->update([
                    'status' => 'resolved',
                    'resolved_by' => auth()->id(),
                    'resolved_at' => now(),
                    'resolution_note' => "Automatically resolved: Content removed via report #{$report->id}",
                ]);
            }

            // Log bulk resolution if applicable
            if ($relatedReports->count() > 0) {
                AdminLog::create([
                    'admin_id' => auth()->id(),
                    'action' => 'bulk_resolve_reports',
                    'target_type' => $reportableType,
                    'target_id' => $reportableId,
                    'reason' => "Bulk resolved {$relatedReports->count()} related reports after content removal",
                    'metadata' => [
                        'primary_report_id' => $report->id,
                        'resolved_report_ids' => $relatedReports->pluck('id')->toArray(),
                        'count' => $relatedReports->count(),
                    ],
                ]);
            }
        }

        return redirect()->route('admin.reports.index')->with('success', 'Report resolved.');
    }
}
