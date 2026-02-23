@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Content Reports</h1>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</div>
                <div class="text-sm text-base-content/60">Pending</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="text-2xl font-bold text-success">{{ $stats['resolved'] }}</div>
                <div class="text-sm text-base-content/60">Resolved</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="text-2xl font-bold">{{ $stats['dismissed'] }}</div>
                <div class="text-sm text-base-content/60">Dismissed</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="text-2xl font-bold">{{ array_sum($stats['by_reason']->toArray()) }}</div>
                <div class="text-sm text-base-content/60">Total</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-4">
                <select name="status" class="select select-bordered select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>

                <select name="reason" class="select select-bordered select-sm">
                    <option value="">All Reasons</option>
                    @foreach (['spam', 'harassment', 'misinformation', 'hate_speech', 'violence', 'other'] as $reason)
                        <option value="{{ $reason }}" {{ request('reason') === $reason ? 'selected' : '' }}>
                            {{ ucfirst($reason) }}
                        </option>
                    @endforeach
                </select>

                <select name="type" class="select select-bordered select-sm">
                    <option value="">All Types</option>
                    <option value="chirp" {{ request('type') === 'chirp' ? 'selected' : '' }}>Chirps</option>
                    <option value="user" {{ request('type') === 'user' ? 'selected' : '' }}>Users</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-ghost btn-sm">Clear</a>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card bg-base-100 overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Reported Content</th>
                    <th>Reported By</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr>
                        <td>
                            @if ($report->reportable_type === 'App\\Models\\Chirp')
                                <span class="badge badge-sm">Chirp</span>
                            @else
                                <span class="badge badge-sm badge-secondary">User</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $report->reason === 'spam' ? 'warning' : 'error' }} badge-sm">
                                {{ $report->reason }}
                            </span>
                        </td>
                        <td>
                            @if ($report->reportable_type === 'App\\Models\\Chirp' && $report->reportable)
                                <p class="text-sm truncate max-w-xs">{{ $report->reportable->message }}</p>
                                <p class="text-xs text-base-content/60">by
                                    {{ $report->reportable->user?->name ?? 'Deleted User' }}</p>
                            @elseif($report->reportable_type === 'App\\Models\\User' && $report->reportable)
                                <div class="flex items-center gap-2">
                                    <div class="avatar">
                                        <div class="w-6 h-6 rounded-full">
                                            <img src="{{ $report->reportable->avatar_url }}" />
                                        </div>
                                    </div>
                                    <span class="text-sm">{{ $report->reportable->name }}</span>
                                </div>
                            @else
                                <span class="text-sm text-base-content/60 italic">Content deleted</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">
                                    <div class="w-6 h-6 rounded-full">
                                        <img src="{{ $report->reporter->avatar_url }}" />
                                    </div>
                                </div>
                                <span class="text-sm">{{ $report->reporter->name }}</span>
                            </div>
                        </td>
                        <td>
                            @if ($report->status === 'pending')
                                <span class="badge badge-warning badge-sm">Pending</span>
                            @elseif($report->status === 'resolved')
                                <span class="badge badge-success badge-sm">Resolved</span>
                            @else
                                <span class="badge badge-ghost badge-sm">Dismissed</span>
                            @endif
                        </td>
                        <td class="text-sm">{{ $report->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.reports.show', $report) }}" class="btn btn-ghost btn-xs">
                                Review
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $reports->links() }}
    </div>
@endsection
