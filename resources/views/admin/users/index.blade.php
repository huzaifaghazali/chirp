@extends('admin.layout')

@section('title', 'User Management')

@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Users</h1>
        <div class="text-sm text-base-content/60">
            {{ $users->total() }} total users
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4 items-end">
                <div class="form-control flex-1 min-w-50">
                    <label class="label">
                        <span class="label-text">Search</span>
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                        class="input input-bordered input-sm" />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <select name="status" class="select select-bordered select-sm">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended
                        </option>
                        <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Role</span>
                    </label>
                    <select name="role" class="select select-bordered select-sm">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin Only</option>
                    </select>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Sort</span>
                    </label>
                    <select name="sort" class="select select-bordered select-sm">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                        <option value="most_chirps" {{ request('sort') === 'most_chirps' ? 'selected' : '' }}>Most Chirps
                        </option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-base-100 overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Chirps</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                    </div>
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    <div class="text-sm text-base-content/60">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($user->status === 'active')
                                <span class="badge badge-success badge-sm">Active</span>
                            @elseif($user->status === 'suspended')
                                <span class="badge badge-warning badge-sm">Suspended</span>
                                @if ($user->suspended_until)
                                    <div class="text-xs text-base-content/60 mt-1">
                                        Until {{ $user->suspended_until->format('M d') }}
                                    </div>
                                @endif
                            @else
                                <span class="badge badge-error badge-sm">Banned</span>
                            @endif
                        </td>
                        <td>
                            @if ($user->is_admin)
                                <span class="badge badge-primary badge-sm">Admin</span>
                            @else
                                <span class="badge badge-ghost badge-sm">User</span>
                            @endif
                        </td>
                        <td>{{ $user->chirps_count ?? $user->chirps()->count() }}</td>
                        <td class="text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-xs">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-ghost btn-xs">Edit</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
