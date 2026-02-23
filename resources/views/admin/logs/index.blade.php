@extends('admin.layout')

@section('title', 'Admin Activity Logs')

@section('content')
    <h1 class="text-3xl font-bold mb-6">Admin Activity Logs</h1>

    <!-- Filters -->
    <div class="card bg-base-100 mb-6">
        <div class="card-body">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Action Type</span>
                    </label>
                    <select name="action" class="select select-bordered select-sm">
                        <option value="">All Actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                                {{ str_replace('_', ' ', $action) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Date From</span>
                    </label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="input input-bordered input-sm" />
                </div>

                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Date To</span>
                    </label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="input input-bordered input-sm" />
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-ghost btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card bg-base-100 overflow-x-auto">
        <table class="table table-zebra w-full">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Reason</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap">
                            <div class="text-sm">{{ $log->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-base-content/60">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">
                                    <div class="w-8 h-8 rounded-full">
                                        <img src="{{ $log->admin->avatar_url }}" alt="{{ $log->admin->name }}" />
                                    </div>
                                </div>
                                <span class="text-sm font-medium">{{ $log->admin->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span
                                class="badge badge-{{ match ($log->action) {
                                    'delete_chirp', 'ban_user', 'delete_user' => 'error',
                                    'suspend_user' => 'warning',
                                    'activate_user', 'dismiss_reports' => 'success',
                                    default => 'primary',
                                } }} badge-sm">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td>
                            @if ($log->target)
                                @if ($log->target_type === 'App\\Models\\User')
                                    <a href="{{ route('admin.users.show', $log->target_id) }}"
                                        class="link link-hover text-sm">
                                        User: {{ $log->target->name ?? 'Deleted' }}
                                    </a>
                                @elseif($log->target_type === 'App\\Models\\Chirp')
                                    <a href="{{ route('admin.chirps.show', $log->target_id) }}"
                                        class="link link-hover text-sm">
                                        Chirp: "{{ Str::limit($log->target->message ?? 'Deleted', 30) }}"
                                    </a>
                                @else
                                    <span class="text-sm text-base-content/60">{{ class_basename($log->target_type) }}
                                        #{{ $log->target_id }}</span>
                                @endif
                            @else
                                <span class="text-sm text-base-content/60 italic">Deleted</span>
                            @endif
                        </td>
                        <td class="max-w-xs">
                            <p class="text-sm truncate" title="{{ $log->reason }}">{{ $log->reason ?? '-' }}</p>
                        </td>
                        <td>
                            @if ($log->metadata)
                                <button onclick="showMetadata{{ $log->id }}.showModal()" class="btn btn-ghost btn-xs">
                                    View
                                </button>
                                <dialog id="showMetadata{{ $log->id }}" class="modal">
                                    <div class="modal-box max-w-2xl">
                                        <h3 class="font-bold text-lg mb-4">Action Metadata</h3>
                                        <pre class="bg-base-200 p-4 rounded-lg text-xs overflow-auto"><code>{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                                        <div class="modal-action">
                                            <button onclick="showMetadata{{ $log->id }}.close()"
                                                class="btn btn-primary">Close</button>
                                        </div>
                                    </div>
                                </dialog>
                            @else
                                <span class="text-sm text-base-content/60">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-base-content/60">
                            No activity logs found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
@endsection
