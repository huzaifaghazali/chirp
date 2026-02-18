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
                <a href="{{ route('profile.show', auth()->user()) }}" class="btn btn-ghost btn-sm flex items-center gap-2">
                    <div class="avatar">
                        <div class="w-6 h-6 rounded-full">
                            <img src="{{ auth()->user()->avatar_url }}" alt="Profile" />
                        </div>
                    </div>
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="/logout" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm">Logout</button>
                </form>
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
    <script>
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        /**
         * Toggle like on a chirp
         */
        async function toggleLike(chirpId, button) {
            // Prevent double-clicks
            if (button.disabled) return;
            button.disabled = true;

            try {
                const response = await fetch(`/chirps/${chirpId}/like`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    updateLikeButton(button, data.liked, data.likes_count);
                    showToast(data.message, 'success');
                } else {
                    showToast('Something went wrong', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to update like', 'error');
            } finally {
                button.disabled = false;
            }
        }

        /**
         * Update like button appearance
         */
        function updateLikeButton(button, liked, count) {
            const icon = button.querySelector('.like-icon');
            const countSpan = button.querySelector('.likes-count');

            button.setAttribute('data-liked', liked ? 'true' : 'false');
            countSpan.textContent = count;

            if (liked) {
                button.classList.remove('text-base-content/60');
                button.classList.add('text-error');
                icon.setAttribute('fill', 'currentColor');
                icon.classList.add('scale-125');
                setTimeout(() => icon.classList.remove('scale-125'), 200);
            } else {
                button.classList.remove('text-error');
                button.classList.add('text-base-content/60');
                icon.setAttribute('fill', 'none');
            }
        }

        /**
         * Show toast notification
         */
        function showToast(message, type = 'success') {
            // Remove existing toasts
            const existing = document.querySelector('.toast-notification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = `toast-notification fixed bottom-4 right-4 z-50 animate-fade-in`;
            toast.innerHTML = `
                <div class="alert ${type === 'success' ? 'alert-success' : 'alert-error'} shadow-lg">
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s';
                setTimeout(() => toast.remove(), 500);
            }, 2000);
        }
    </script>

    <!-- Add fade-in animation -->
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
</body>

</html>
