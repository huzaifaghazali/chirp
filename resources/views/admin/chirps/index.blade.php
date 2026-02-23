@extends('admin.layout')

@section('title', 'Chirp Moderation')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Chirp Moderation</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.chirps.index', ['filter' => 'reported']) }}"
                class="btn {{ request('filter') === 'reported' ? 'btn-primary' : 'btn-ghost' }} btn-sm">
                Reported Only
            </a>
            <a href="{{ route('admin.chirps.index') }}"
                class="btn {{ !request('filter') ? 'btn-primary' : 'btn-ghost' }} btn-sm">
                All Chirps
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.chirps.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="form-control flex-1 min-w-[300px]">
                    <label class="label">
                        <span class="label-text">Search Chirps</span>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search in chirp content..." class="input input-bordered input-sm" />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">User Status</span>
                    </label>
                    <select name="user_status" class="select select-bordered select-sm">
                        <option value="">All Users</option>
                        <option value="active" {{ request('user_status') === 'active' ? 'selected' : '' }}>Active Users
                        </option>
                        <option value="suspended" {{ request('user_status') === 'suspended' ? 'selected' : '' }}>Suspended
                            Users</option>
                        <option value="banned" {{ request('user_status') === 'banned' ? 'selected' : '' }}>Banned Users
                        </option>
                    </select>
                </div>

                <input type="hidden" name="filter" value="{{ request('filter') }}">

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.chirps.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Chirps Grid -->
    <div class="grid grid-cols-1 gap-4">
        @forelse($chirps as $chirp)
            <div
                class="card bg-base-100 {{ $chirp->reports->where('status', 'pending')->count() > 0 ? 'border-error border-2' : '' }}">
                <div class="card-body">
                    <div class="flex justify-between items-start">
                        <!-- User Info -->
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-10 h-10 rounded-full">
                                    <img src="{{ $chirp->user?->avatar_url ?? 'https://avatars.laravel.cloud/default' }}"
                                        alt="{{ $chirp->user?->name ?? 'Deleted User' }}" />
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">{{ $chirp->user?->name ?? 'Deleted User' }}</span>
                                    @if ($chirp->user)
                                        @if ($chirp->user->status === 'suspended')
                                            <span class="badge badge-warning badge-sm">Suspended</span>
                                        @elseif($chirp->user->status === 'banned')
                                            <span class="badge badge-error badge-sm">Banned</span>
                                        @endif
                                        @if ($chirp->user->is_admin)
                                            <span class="badge badge-primary badge-sm">Admin</span>
                                        @endif
                                    @endif
                                </div>
                                <div class="text-xs text-base-content/60">
                                    {{ $chirp->created_at->format('M d, Y H:i') }}
                                    · {{ $chirp->likes_count }} likes
                                    @if ($chirp->created_at != $chirp->updated_at)
                                        · <span class="italic">edited</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Report Badge -->
                        @php
                            $pendingReports = $chirp->reports->where('status', 'pending')->count();
                        @endphp
                        @if ($pendingReports > 0)
                            <div class="badge badge-error gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                {{ $pendingReports }} report{{ $pendingReports > 1 ? 's' : '' }}
                            </div>
                        @endif
                    </div>

                    <!-- Chirp Content -->
                    <div class="mt-4 p-4 bg-base-200 rounded-lg">
                        <p class="text-lg">{{ $chirp->message }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-between items-center mt-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.chirps.show', $chirp) }}" class="btn btn-ghost btn-sm">
                                View Details
                            </a>
                            @if ($chirp->user)
                                <a href="{{ route('admin.users.show', $chirp->user) }}" class="btn btn-ghost btn-sm">
                                    View Author
                                </a>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            @if ($pendingReports > 0)
                                <form method="POST" action="{{ route('admin.chirps.dismiss', $chirp) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                        onclick="return confirm('Dismiss all {{ $pendingReports }} reports for this chirp?')">
                                        Dismiss Reports
                                    </button>
                                </form>
                            @endif
                            <button onclick="deleteChirpModal{{ $chirp->id }}.showModal()" class="btn btn-error btn-sm">
                                Delete Chirp
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Modal for each chirp -->
            <dialog id="deleteChirpModal{{ $chirp->id }}" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg text-error">Delete Chirp</h3>
                    <div class="py-4">
                        <p class="mb-4">Are you sure you want to delete this chirp?</p>
                        <div class="bg-base-200 p-3 rounded text-sm mb-4">
                            "{{ Str::limit($chirp->message, 100) }}"
                        </div>
                        <p class="text-sm text-base-content/60">
                            This will also resolve all pending reports for this chirp.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('admin.chirps.destroy', $chirp) }}">
                        @csrf
                        @method('DELETE')
                        <div class="form-control mb-4">
                            <label class="label">
                                <span class="label-text">Reason for deletion (required)</span>
                            </label>
                            <textarea name="reason" class="textarea textarea-bordered" rows="2" required
                                placeholder="e.g., Violates community guidelines, spam, harassment..."></textarea>
                        </div>
                        <div class="modal-action">
                            <button type="button" onclick="deleteChirpModal{{ $chirp->id }}.close()"
                                class="btn btn-ghost">Cancel</button>
                            <button type="submit" class="btn btn-error">Delete Permanently</button>
                        </div>
                    </form>
                </div>
            </dialog>
        @empty
            <div class="card bg-base-100">
                <div class="card-body text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto opacity-30 mb-4" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium">No chirps found</h3>
                    <p class="text-base-content/60 mt-2">Try adjusting your filters</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $chirps->links() }}
    </div>
@endsection
