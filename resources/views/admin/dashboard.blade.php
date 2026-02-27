@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Users Card -->
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/60">Total Users</p>
                        <h2 class="text-3xl font-bold">{{ number_format($stats['total_users']) }}</h2>
                    </div>
                    <div class="badge badge-primary">+{{ $stats['new_users_today'] }} today</div>
                </div>
                <div class="mt-2 text-sm text-base-content/60">
                    {{ $stats['new_users_this_week'] }} this week
                </div>
            </div>
        </div>

        <!-- Chirps Card -->
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/60">Total Chirps</p>
                        <h2 class="text-3xl font-bold">{{ number_format($stats['total_chirps']) }}</h2>
                    </div>
                    <div class="badge badge-secondary">+{{ $stats['chirps_today'] }} today</div>
                </div>
                <div class="mt-2 text-sm text-base-content/60">
                    {{ $stats['chirps_this_week'] }} this week
                </div>
            </div>
        </div>

        <!-- Likes Card -->
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/60">Total Likes</p>
                        <h2 class="text-3xl font-bold">{{ number_format($stats['total_likes']) }}</h2>
                    </div>
                    <div class="badge badge-accent">+{{ $stats['likes_today'] }} today</div>
                </div>
            </div>
        </div>

        <!-- Reports Card -->
        <div class="card bg-base-100 {{ $stats['pending_reports'] > 0 ? 'border-error' : '' }}">
            <div class="card-body">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-base-content/60">Pending Reports</p>
                        <h2 class="text-3xl font-bold {{ $stats['pending_reports'] > 0 ? 'text-error' : '' }}">
                            {{ $stats['pending_reports'] }}
                        </h2>
                    </div>
                    <div class="badge badge-ghost">{{ $stats['total_reports'] }} total</div>
                </div>
                @if ($stats['pending_reports'] > 0)
                    <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}"
                        class="btn btn-error btn-xs mt-3">
                        Review Now
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- User Registration Chart -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">User Registrations (30 Days)</h3>
                <canvas id="userChart" height="250" data-labels='{!! json_encode($userChartData->pluck('date')) !!}'
                    data-data='{!! json_encode($userChartData->pluck('count')) !!}'></canvas>
            </div>
        </div>

        <!-- Chirps Chart -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">Chirps Posted (30 Days)</h3>
                <canvas id="chirpChart" height="250" data-labels='{!! json_encode($chirpChartData->pluck('date')) !!}'
                    data-data='{!! json_encode($chirpChartData->pluck('count')) !!}'></canvas>
            </div>
        </div>
    </div>

    <!-- User Status & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Status Distribution -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">User Status</h3>
                <div class="space-y-3 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-success"></span>
                            Active
                        </span>
                        <span class="font-semibold">{{ $stats['active_users'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-warning"></span>
                            Suspended
                        </span>
                        <span class="font-semibold">{{ $stats['suspended_users'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-error"></span>
                            Banned
                        </span>
                        <span class="font-semibold">{{ $stats['banned_users'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">Recent Users</h3>
                <div class="space-y-3 mt-4">
                    @foreach ($recentUsers as $user)
                        <div class="flex items-center gap-3">
                            <div class="avatar">
                                <div class="w-8 h-8 rounded-full">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $user->created_at->diffForHumans() }}</p>
                            </div>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-xs">View</a>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm w-full mt-4">View All Users</a>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="card bg-base-100">
            <div class="card-body">
                <h3 class="card-title">Pending Reports</h3>
                <div class="space-y-3 mt-4">
                    @forelse($recentReports as $report)
                        <div class="flex items-start gap-3">
                            <div class="badge badge-error badge-sm">{{ $report->reason }}</div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm truncate">
                                    {{ $report->reportable_type === 'App\\Models\\Chirp' ? 'Chirp' : 'User' }}
                                    reported by {{ $report->reporter->name }}
                                </p>
                                <p class="text-xs text-base-content/60">{{ $report->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-base-content/60 py-4">No pending reports 🎉</p>
                    @endforelse
                </div>
                @if ($stats['pending_reports'] > 0)
                    <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}"
                        class="btn btn-ghost btn-sm w-full mt-4">
                        Review {{ $stats['pending_reports'] }} Reports
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script></script>
    @vite(['resources/js/admin-dashboard.js'])
@endsection
