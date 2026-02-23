@extends('admin.layout')

@section('title', $user->name)

@section('content')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">← Back to Users</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Profile Card -->
        <div class="card bg-base-100 lg:col-span-1">
            <div class="card-body">
                <div class="flex flex-col items-center text-center">
                    <div class="avatar mb-4">
                        <div class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                    <p class="text-base-content/60">{{ $user->email }}</p>

                    <div class="flex gap-2 mt-4">
                        @if ($user->status === 'active')
                            <span class="badge badge-success">Active</span>
                        @elseif($user->status === 'suspended')
                            <span class="badge badge-warning">Suspended</span>
                        @else
                            <span class="badge badge-error">Banned</span>
                        @endif

                        @if ($user->is_admin)
                            <span class="badge badge-primary">Admin</span>
                        @endif
                    </div>

                    @if ($user->suspended_until)
                        <p class="text-warning text-sm mt-2">
                            Suspended until {{ $user->suspended_until->format('F d, Y H:i') }}
                        </p>
                    @endif
                </div>

                <div class="divider"></div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Joined</span>
                        <span>{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Last Updated</span>
                        <span>{{ $user->updated_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Actions -->
                <div class="space-y-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm w-full">
                        Edit User
                    </a>

                    @if ($user->status !== 'active')
                        <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="w-full">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-full">
                                Activate Account
                            </button>
                        </form>
                    @endif

                    @if ($user->status !== 'suspended' && $user->status !== 'banned')
                        <button onclick="suspendModal.showModal()" class="btn btn-warning btn-sm w-full">
                            Suspend User
                        </button>
                    @endif

                    @if ($user->status !== 'banned')
                        <button onclick="banModal.showModal()" class="btn btn-error btn-sm w-full">
                            Ban User
                        </button>
                    @endif

                    <button onclick="deleteModal.showModal()" class="btn btn-error btn-outline btn-sm w-full">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats & Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="card bg-base-100">
                    <div class="card-body p-4 text-center">
                        <div class="text-2xl font-bold">{{ $stats['total_chirps'] }}</div>
                        <div class="text-sm text-base-content/60">Chirps</div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4 text-center">
                        <div class="text-2xl font-bold">{{ $stats['total_likes_received'] }}</div>
                        <div class="text-sm text-base-content/60">Likes Received</div>
                    </div>
                </div>
                <div class="card bg-base-100">
                    <div class="card-body p-4 text-center">
                        <div class="text-2xl font-bold">{{ $stats['total_likes_given'] }}</div>
                        <div class="text-sm text-base-content/60">Likes Given</div>
                    </div>
                </div>
                <div class="card bg-base-100 {{ $stats['reports_against'] > 0 ? 'border-error' : '' }}">
                    <div class="card-body p-4 text-center">
                        <div class="text-2xl font-bold {{ $stats['reports_against'] > 0 ? 'text-error' : '' }}">
                            {{ $stats['reports_against'] }}
                        </div>
                        <div class="text-sm text-base-content/60">Reports Against</div>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            @if ($user->bio)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title text-base">Bio</h3>
                        <p class="mt-2">{{ $user->bio }}</p>
                    </div>
                </div>
            @endif

            <!-- Recent Chirps -->
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="card-title text-base mb-4">Recent Chirps</h3>
                    <div class="space-y-4">
                        @forelse($user->chirps as $chirp)
                            <div class="border-l-4 border-base-300 pl-4 py-2">
                                <p class="text-sm">{{ $chirp->message }}</p>
                                <div class="flex justify-between items-center mt-2 text-xs text-base-content/60">
                                    <span>{{ $chirp->created_at->diffForHumans() }}</span>
                                    <span>{{ $chirp->likes_count }} likes</span>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('admin.chirps.show', $chirp) }}" class="btn btn-ghost btn-xs">
                                        View in Moderation
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-base-content/60 py-4">No chirps yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Admin Log -->
            @if ($adminLogs->count() > 0)
                <div class="card bg-base-100">
                    <div class="card-body">
                        <h3 class="card-title text-base mb-4">Admin Actions History</h3>
                        <div class="space-y-3">
                            @foreach ($adminLogs as $log)
                                <div class="flex items-start gap-3 text-sm">
                                    <div class="badge badge-sm">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </div>
                                    <div class="flex-1">
                                        <p>by {{ $log->admin->name }} {{ $log->created_at->diffForHumans() }}</p>
                                        @if ($log->reason)
                                            <p class="text-base-content/60 text-xs mt-1">Reason: {{ $log->reason }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Suspend Modal -->
    <dialog id="suspendModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Suspend User</h3>
            <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                @csrf
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Suspension Duration</span>
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
                        <span class="label-text">Reason (required)</span>
                    </label>
                    <textarea name="reason" class="textarea textarea-bordered" rows="3" required
                        placeholder="Explain why this user is being suspended..."></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="suspendModal.close()" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend User</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Ban Modal -->
    <dialog id="banModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error">Ban User Permanently</h3>
            <p class="py-4">This will permanently ban {{ $user->name }}. They will not be able to create new accounts
                with this email.</p>
            <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                @csrf
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Reason (required)</span>
                    </label>
                    <textarea name="reason" class="textarea textarea-bordered" rows="3" required
                        placeholder="Explain why this user is being banned..."></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="banModal.close()" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-error">Ban Permanently</button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Delete Modal -->
    <dialog id="deleteModal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error">Delete User Account</h3>
            <p class="py-4">This will permanently delete {{ $user->name }} and all their chirps, likes, and data. This
                action cannot be undone.</p>
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                @csrf
                @method('DELETE')
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Type user's email to confirm: <strong>{{ $user->email }}</strong></span>
                    </label>
                    <input type="text" name="confirm_email" class="input input-bordered" required
                        placeholder="{{ $user->email }}" />
                </div>
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Reason for deletion (required)</span>
                    </label>
                    <textarea name="reason" class="textarea textarea-bordered" rows="3" required></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" onclick="deleteModal.close()" class="btn btn-ghost">Cancel</button>
                    <button type="submit" class="btn btn-error">Delete Permanently</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
