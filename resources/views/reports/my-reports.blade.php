<x-layout>
    <x-slot:title>
        My Reports
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mt-8">My Reports</h1>
        <p class="text-base-content/60 mt-2">Track the status of content you've reported</p>

        <div class="space-y-4 mt-6">
            @forelse($reports as $report)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-2">
                                @if ($report->reportable_type === 'App\\Models\\Chirp')
                                    <span class="badge badge-sm">Chirp</span>
                                @else
                                    <span class="badge badge-sm badge-secondary">User</span>
                                @endif
                                <span
                                    class="badge badge-{{ $report->reason === 'spam' ? 'warning' : 'error' }} badge-sm">
                                    {{ str_replace('_', ' ', $report->reason) }}
                                </span>
                            </div>
                            <span class="text-sm text-base-content/60">{{ $report->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="mt-3">
                            @if ($report->reportable_type === 'App\\Models\\Chirp' && $report->reportable)
                                <p class="text-sm line-clamp-2">{{ $report->reportable->message }}</p>
                                <p class="text-xs text-base-content/60 mt-1">by
                                    {{ $report->reportable->user?->name ?? 'Deleted User' }}</p>
                            @elseif($report->reportable_type === 'App\\Models\\User' && $report->reportable)
                                <div class="flex items-center gap-2">
                                    <div class="avatar">
                                        <div class="w-8 h-8 rounded-full">
                                            <img src="{{ $report->reportable->avatar_url }}" />
                                        </div>
                                    </div>
                                    <span class="font-medium">{{ $report->reportable->name }}</span>
                                </div>
                            @else
                                <p class="text-sm text-base-content/60 italic">Content has been removed</p>
                            @endif
                        </div>

                        @if ($report->details)
                            <div class="mt-3 p-2 bg-base-200 rounded text-sm">
                                <span class="text-base-content/60">Your note:</span> {{ $report->details }}
                            </div>
                        @endif

                        <div class="divider my-3"></div>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-2">
                                @if ($report->status === 'pending')
                                    <span class="badge badge-warning badge-sm gap-1">
                                        <span class="animate-pulse">●</span> Pending Review
                                    </span>
                                @elseif($report->status === 'under_review')
                                    <span class="badge badge-info badge-sm">Under Review</span>
                                @elseif($report->status === 'resolved')
                                    <span class="badge badge-success badge-sm">Resolved</span>
                                @else
                                    <span class="badge badge-ghost badge-sm">Dismissed</span>
                                @endif
                            </div>

                            @if ($report->resolved_at && $report->resolution_note)
                                <div class="text-xs text-base-content/60 text-right max-w-xs">
                                    {{ $report->resolution_note }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="card bg-base-100">
                    <div class="card-body text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto opacity-30 mb-4" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-medium">No reports yet</h3>
                        <p class="text-base-content/60 mt-2">You haven't reported any content.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($reports->hasPages())
            <div class="mt-6">
                {{ $reports->links() }}
            </div>
        @endif
    </div>
</x-layout>
