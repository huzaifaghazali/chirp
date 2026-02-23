@extends('admin.layout')

@section('title', 'Report Details')

@section('content')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost btn-sm">← Back to Reports</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Report Info -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h2 class="card-title mb-4">Report #{{ $report->id }}</h2>

                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-base-200 rounded-lg">
                        <span class="text-sm text-base-content/60">Status</span>
                        @if ($report->status === 'pending')
                            <span class="badge badge-warning">Pending Review</span>
                        @elseif($report->status === 'resolved')
                            <span class="badge badge-success">Resolved</span>
                        @elseif($report->status === 'dismissed')
                            <span class="badge badge-ghost">Dismissed</span>
                        @else
                            <span class="badge badge-primary">Under Review</span>
                        @endif
                    </div>

                    <div class="flex justify-between items-center p-3 bg-base-200 rounded-lg">
                        <span class="text-sm text-base-content/60">Reason</span>
                        <span class="badge badge-{{ $report->reason === 'spam' ? 'warning' : 'error' }}">
                            {{ ucfirst(str_replace('_', ' ', $report->reason)) }}
                        </span>
                    </div>

                    <div class="p-3 bg-base-200 rounded-lg">
                        <span class="text-sm text-base-content/60 block mb-2">Reported By</span>
                        <div class="flex items-center gap-2">
                            <div class="avatar">
                                <div class="w-8 h-8 rounded-full">
                                    <img src="{{ $report->reporter->avatar_url }}" />
                                </div>
                            </div>
                            <span class="font-medium">{{ $report->reporter->name }}</span>
                            <span class="text-sm text-base-content/60">· {{ $report->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    @if ($report->details)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-base-content/60 block mb-2">Additional Details</span>
                            <p class="text-sm">{{ $report->details }}</p>
                        </div>
                    @endif

                    @if ($report->resolved_at)
                        <div class="p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-base-content/60 block mb-2">Resolution</span>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="avatar">
                                    <div class="w-6 h-6 rounded-full">
                                        <img src="{{ $report->resolver->avatar_url }}" />
                                    </div>
                                </div>
                                <span class="text-sm">Resolved by {{ $report->resolver->name }}</span>
                                <span class="text-xs text-base-content/60">·
                                    {{ $report->resolved_at->diffForHumans() }}</span>
                            </div>
                            @if ($report->resolution_note)
                                <p class="text-sm mt-2">{{ $report->resolution_note }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                @if ($report->status === 'pending')
                    <div class="divider"></div>
                    <h3 class="font-semibold mb-4">Take Action</h3>
                    <form method="POST" action="{{ route('admin.reports.resolve', $report) }}" class="space-y-4">
                        @csrf
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Resolution Action</span>
                            </label>
                            <select name="action" class="select select-bordered" required>
                                <option value="">Select action...</option>
                                @if ($report->reportable_type === 'App\\Models\\Chirp')
                                    <option value="content_removed">Remove Content</option>
                                @endif
                                <option value="warning_issued">Issue Warning</option>
                                <option value="dismissed">Dismiss Report</option>
                            </select>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Resolution Note (required)</span>
                            </label>
                            <textarea name="resolution_note" class="textarea textarea-bordered" rows="3" required
                                placeholder="Explain the resolution..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-full">Submit Resolution</button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Reported Content Preview -->
        <div>
            @if ($report->reportable_type === 'App\\Models\\Chirp' && $report->reportable)
                <div class="card bg-base-100 {{ $report->status === 'pending' ? 'border-error border-2' : '' }}">
                    <div class="card-body">
                        <h3 class="card-title text-base mb-4">Reported Chirp</h3>

                        <div class="flex items-center gap-3 mb-4">
                            <div class="avatar">
                                <div class="w-10 h-10 rounded-full">
                                    <img
                                        src="{{ $report->reportable->user?->avatar_url ?? 'https://avatars.laravel.cloud/default' }}" />
                                </div>
                            </div>
                            <div>
                                <p class="font-semibold">{{ $report->reportable->user?->name ?? 'Deleted User' }}</p>
                                <p class="text-xs text-base-content/60">
                                    {{ $report->reportable->created_at->diffForHumans() }}</p>
                            </div>
                        </div>

                        <div class="bg-base-200 p-4 rounded-lg mb-4">
                            <p>{{ $report->reportable->message }}</p>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.chirps.show', $report->reportable) }}" class="btn btn-primary btn-sm">
                                View Full Details
                            </a>
                            @if ($report->status === 'pending')
                                <form method="POST" action="{{ route('admin.chirps.destroy', $report->reportable) }}"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="reason"
                                        value="Report #{{ $report->id }}: {{ $report->reason }}">
                                    <button type="submit" class="btn btn-error btn-sm"
                                        onclick="return confirm('Delete this chirp?')">
                                        Quick Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($report->reportable_type === 'App\\Models\\User' && $report->reportable)
                <div class="card bg-base-100 {{ $report->status === 'pending' ? 'border-error border-2' : '' }}">
                    <div class="card-body">
                        <h3 class="card-title text-base mb-4">Reported User</h3>

                        <div class="flex items-center gap-4 mb-4">
                            <div class="avatar">
                                <div class="w-16 h-16 rounded-full">
                                    <img src="{{ $report->reportable->avatar_url }}" />
                                </div>
                            </div>
                            <div>
                                <p class="text-xl font-bold">{{ $report->reportable->name }}</p>
                                <p class="text-sm text-base-content/60">{{ $report->reportable->email }}</p>
                                <div class="flex gap-2 mt-2">
                                    @if ($report->reportable->status === 'active')
                                        <span class="badge badge-success badge-sm">Active</span>
                                    @elseif($report->reportable->status === 'suspended')
                                        <span class="badge badge-warning badge-sm">Suspended</span>
                                    @else
                                        <span class="badge badge-error badge-sm">Banned</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('admin.users.show', $report->reportable) }}"
                            class="btn btn-primary btn-sm w-full">
                            View User Profile
                        </a>
                    </div>
                </div>
            @else
                <div class="card bg-base-100">
                    <div class="card-body text-center py-8">
                        <p class="text-base-content/60">Content has been deleted</p>
                    </div>
                </div>
            @endif

            <!-- Other Reports for Same Content -->
            @php
                $otherReports = \App\Models\Report::where('reportable_type', $report->reportable_type)
                    ->where('reportable_id', $report->reportable_id)
                    ->where('id', '!=', $report->id)
                    ->with('reporter')
                    ->latest()
                    ->take(5)
                    ->get();
            @endphp

            @if ($otherReports->count() > 0)
                <div class="card bg-base-100 mt-6">
                    <div class="card-body">
                        <h3 class="card-title text-base mb-4">Other Reports ({{ $otherReports->count() }})</h3>
                        <div class="space-y-3">
                            @foreach ($otherReports as $other)
                                <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg text-sm">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="badge badge-{{ $other->reason === 'spam' ? 'warning' : 'error' }} badge-sm">
                                            {{ $other->reason }}
                                        </span>
                                        <span class="text-base-content/60">by {{ $other->reporter->name }}</span>
                                    </div>
                                    <span
                                        class="text-xs text-base-content/60">{{ $other->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
