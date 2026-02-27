<!DOCTYPE html>
<html lang="en" data-theme="laravelChirper">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - Chirper' : 'Chirper' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col bg-base-200 font-sans">
    <nav class="navbar bg-base-100 sticky top-0 z-50">
        <div class="navbar-start gap-2">
            <a href="/" class="btn btn-ghost text-xl">🐦 Chirper</a>

            <!-- Search Bar - Desktop -->
            <form method="GET" action="{{ route('search') }}" class="hidden md:flex ml-4">
                <div class="relative">
                    <input type="text" name="q" placeholder="Search..."
                        class="input input-bordered input-sm w-64 pl-9" value="{{ request('q') }}" />
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </form>
        </div>

        <div class="navbar-center md:hidden">
            <!-- Mobile Search Button -->
            <a href="{{ route('search') }}" class="btn btn-ghost btn-circle">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </a>
        </div>

        <div class="navbar-end gap-2">
            @auth
                {{-- Profile dropdown with Admin option --}}
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-ghost btn-sm flex items-center gap-2">
                        <div class="avatar">
                            <div class="w-6 h-6 rounded-full">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Profile" />
                            </div>
                        </div>
                        <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-52 mt-4">
                        <li><a href="{{ route('profile.show', auth()->user()) }}">Profile</a></li>
                        <li><a href="{{ route('profile.edit', auth()->user()) }}">Settings</a></li>
                        <li>
                            <a href="{{ route('reports.my') }}">
                                My Reports
                                @php
                                    $pendingReports = \App\Models\Report::where('reporter_id', auth()->id())
                                        ->whereIn('status', ['pending', 'under_review'])
                                        ->count();
                                @endphp
                                @if ($pendingReports > 0)
                                    <span class="badge badge-warning badge-sm">{{ $pendingReports }}</span>
                                @endif
                            </a>
                        </li>
                        @if (auth()->user()->is_admin)
                            <li class="divider"></li>
                            <li>
                                <a href="{{ route('admin.dashboard') }}" class="text-primary font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    Admin Panel
                                </a>
                            </li>
                        @endif
                        <li class="divider"></li>
                        <li>
                            <form method="POST" action="/logout" class="w-full">
                                @csrf
                                <button type="submit" class="w-full text-left">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="/login" class="btn btn-ghost btn-sm">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign Up</a>
            @endauth
        </div>
    </nav>

    <!-- Success Toast -->
    @if (session('success'))
        <div class="toast toast-top toast-center">
            <div class="alert alert-success animate-fade-out">
                <svg xmlns="<http://www.w3.org/2000/svg>" class="h-6 w-6 shrink-0 stroke-current" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <main class="flex-1 container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="footer footer-center p-5 bg-base-300 text-base-content text-xs">
        <div>
            <p>© {{ date('Y') }} Chirper - Built with Laravel and ❤️</p>
        </div>
    </footer>

    <!-- Like Functionality JavaScript -->
    <script></script>

    <!-- Add fade-in animation -->
    <style></style>
</body>

</html>
