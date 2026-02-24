@extends('admin.layout')

@section('title', 'Chirp Details')

@section('content')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.chirps.index') }}" class="btn btn-ghost btn-sm">← Back to Chirps</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chirp Card -->
        <div class="lg:col-span-2">
            <div
                class="card bg-base-100 {{ $chirp->reports->where('status', 'pending')->count() > 0 ? 'border-error border-2' : '' }}">
                <div class="card-body">
                    <!-- Author Info -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="avatar">
                            <div class="w-14 h-14 rounded-full">
                                <img src="{{ $chirp->user?->avatar_url ?? 'https://avatars.laravel.cloud/default' }}"
                                    alt="{{ $chirp->user?->name ?? 'Deleted User' }}" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-bold">{{ $chirp->user?->name ?? 'Deleted User' }}</h2>
                                @if ($chirp->user)
                                    @if ($chirp->user->status === 'suspended')
                                        <span class="badge badge-warning">Suspended</span>
                                    @elseif($chirp->user->status === 'banned')
                                        <span class="badge badge-error">Banned</span>
                                    @endif
                                    @if ($chirp->user->is_admin)
                                        <span class="badge badge-primary">Admin</span>
                                    @endif
                                @endif
                            </div>
                            <div class="text-sm text-base-content/60">
                                Posted {{ $chirp->created_at->format('F d, Y \a\t h:i A') }}
                                @if ($chirp->created_at != $chirp->updated_at)
                                    · <span class="italic">Edited {{ $chirp->updated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Chirp Content -->
                    <div class="bg-base-200 p-6 rounded-xl mb-6">
                        <p class="text-xl leading-relaxed">{{ $chirp->message }}</p>
                    </div>

                    <!-- Stats -->
                    <div class="flex gap-6 text-sm mb-6">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/60" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span>{{ $chirp->likes_count }} likes</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-base-content/60" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            <span>{{ $chirp->reports->count() }} reports</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3">
                        @if ($chirp->user)
                            <a href="{{ route('admin.users.show', $chirp->user) }}" class="btn btn-ghost">
                                View Author Profile
                            </a>
                        @endif

                        @php
                            $pendingReports = $chirp->reports->where('status', 'pending')->count();
                        @endphp

                        @if ($pendingReports > 0)
                            <form method="POST" action="{{ route('admin.chirps.dismiss', $chirp) }}" class="inline">
                                @csrf
                                <button type="submit" class="btn btn-ghost"
                                    onclick="return confirm('Dismiss all {{ $pendingReports }} pending reports?')">
                                    Dismiss Reports
                                </button>
                            </form>
                        @endif

                        <button onclick="deleteModal.showModal()" class="btn btn-error">
                            Delete Chirp
                        </button>
                    </div>
                </div>
            </div>

            <!-- Admin Log for this Chirp -->
            @php
                $adminLogs = \App\Models\AdminLog::where('target_type', \App\Models\Chirp::class)
                    ->where('target_id', $chirp->id)
                    ->with('admin')
                    ->latest()
                    ->take(5)
                    ->get();
            @endphp

            @if ($adminLogs->count() > 0)
                <div class="card bg-base-100 mt-6">
                    <div class="card-body">
                        <h3 class="card-title text-base">Admin Action History</h3>
                        <div class="space-y-3 mt-4">
                            @foreach ($adminLogs as $log)
                                <div class="flex items-start gap-3 p-3 bg-base-200 rounded-lg text-sm">
                                    <div class="badge badge-sm">{{ str_replace('_', ' ', $log->action) }}</div>
                                    <div class="flex-1">
                                        <p>by {{ $log->admin->name }} · {{ $log->created_at->diffForHumans() }}</p>
                                        @if ($log->reason)
                                            <p class="text-base-content/60 mt-1">{{ $log->reason }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Reports Sidebar -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title text-base mb-4">
                        Reports
                        @if ($reports->where('status', 'pending')->count() > 0)
                            <span class="badge badge-error badge-sm">{{ $reports->where('status', 'pending')->count() }}
                                pending</span>
                        @endif
                    </h3>

                    <div class="space-y-4 max-h-150 overflow-y-auto">
                        @forelse($reports as $report)
                            <div
                                class="border-l-4 {{ $report->status === 'pending' ? 'border-error' : ($report->status === 'resolved' ? 'border-success' : 'border-base-300') }} pl-4 py-2">
                                <div class="flex items-center gap-2 mb-2">
                                    <span
                                        class="badge badge-{{ $report->reason === 'spam' ? 'warning' : 'error' }} badge-sm">
                                        {{ $report->reason }}
                                    </span>
                                    <span
                                        class="text-xs text-base-content/60">{{ $report->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="flex items-center gap-2 mb-2">
                                    <div class="avatar">
                                        <div class="w-6 h-6 rounded-full">
                                            <img src="{{ $report->reporter->avatar_url }}" />
                                        </div>
                                    </div>
                                    <span class="text-sm">{{ $report->reporter->name }}</span>
                                </div>

                                @if ($report->details)
                                    <p class="text-sm text-base-content/80 mt-2">{{ $report->details }}</p>
                                @endif

                                <div class="mt-2">
                                    @if ($report->status === 'pending')
                                        <span class="badge badge-warning badge-sm">Pending</span>
                                    @elseif($report->status === 'resolved')
                                        <span class="badge badge-success badge-sm">Resolved</span>
                                        <p class="text-xs text-base-content/60 mt-1">
                                            by {{ $report->resolver?->name ?? 'System' }}
                                        </p>
                                    @else
                                        <span class="badge badge-ghost badge-sm">Dismissed</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-base-content/60 py-4">No reports for this chirp</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Quick User Actions -->
            @if ($chirp->user)
                <div class="card bg-base-100 mt-6">
                    <div class="card-body">
                        <h3 class="card-title text-base mb-4">Author Actions</h3>
                        <div class="space-y-2">
                            @if ($chirp->user->status === 'active')
                                <button onclick="suspendUserModal.showModal()" class="btn btn-warning btn-sm w-full">
                                    Suspend Author
                                </button>
                                <button onclick="banUserModal.showModal()" class="btn btn-error btn-sm w-full">
                                    Ban Author
                                </button>
                            @else
                                <form method="POST" action="{{ route('admin.users.activate', $chirp->user) }}"
                                    class="w-full">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-full">
                                        Reactivate Author
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Chirp Modal -->
    <dialog id="deleteModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error">Delete Chirp</h3>
            <div class="py-4">
                <p class="mb-4">This action cannot be undone. The chirp will be permanently removed.</p>
                <div class="bg-base-200 p-3 rounded text-sm mb-4">
                    "{{ Str::limit($chirp->message, 100) }}"
                </div>
            </div>
            <form method="POST" action="{{ route('admin.chirps.destroy', $chirp) }}">
                @csrf
                @method('DELETE')
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Reason for deletion (required)</span>
                    </label>
                    <textarea name="reason" class="textarea textarea-bordered" rows="2" required
                        placeholder="e.g., Violates community guidelines..."></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="deleteModal.close()" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-error">Delete Permanently</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Suspend User Modal -->
    @if ($chirp->user)
        <dialog id="suspendUserModal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Suspend {{ $chirp->user->name }}</h3>
                <form method="POST" action="{{ route('admin.users.suspend', $chirp->user) }}">
                    @csrf
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Duration</span>
                        </label>
                        <select name="duration" class="select select-bordered" required>
                            <option value="1_day">1 Day</option>
                            <option value="3_days">3 Days</option>
                            <option value="7_days">7 Days</option>
                            <option value="30_days">30 Days</option>
                            <option value="permanent">Permanent</option>
                        </select>
                    </div>
                    <div class="form-control mt-4">
                        <label class="label">
                            <span class="label-text">Reason</span>
                        </label>
                        <textarea name="reason" class="textarea textarea-bordered" rows="2" required></textarea>
                    </div>
                    <div class="modal-action">
                        <button type="button" onclick="suspendUserModal.close()" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-warning">Suspend User</button>
                    </div>
                </form>
            </div>
        </dialog>

        <dialog id="banUserModal" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg text-error">Ban {{ $chirp->user->name }}</h3>
                <p class="py-4">This will permanently ban the user. They cannot create new accounts with this email.</p>
                <form method="POST" action="{{ route('admin.users.ban', $chirp->user) }}">
                    @csrf
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Reason</span>
                        </label>
                        <textarea name="reason" class="textarea textarea-bordered" rows="2" required></textarea>
                    </div>
                    <div class="modal-action">
                        <button type="button" onclick="banUserModal.close()" class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-error">Ban Permanently</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@endsection
