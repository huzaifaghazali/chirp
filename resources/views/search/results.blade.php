<x-layout>
    <x-slot:title>
        Search Results{{ $query ? ' for "' . $query . '"' : '' }}
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <!-- Search Header -->
        <h1 class="text-3xl font-bold mt-8">Search</h1>

        <!-- Search Form -->
        <div class="card bg-base-100 mt-6">
            <div class="card-body">
                <form method="GET" action="{{ route('search') }}" class="space-y-4">
                    <div class="flex gap-2">
                        <div class="form-control flex-1">
                            <input type="text" name="q" value="{{ $query }}"
                                placeholder="Search users or chirps..." class="input input-bordered w-full"
                                autocomplete="off" autofocus />
                        </div>
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="flex gap-2">
                        <a href="{{ route('search', ['q' => $query, 'filter' => 'all']) }}"
                            class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-ghost' }}">
                            All
                        </a>
                        <a href="{{ route('search', ['q' => $query, 'filter' => 'users']) }}"
                            class="btn btn-sm {{ $filter === 'users' ? 'btn-primary' : 'btn-ghost' }}">
                            Users ({{ $users->count() }})
                        </a>
                        <a href="{{ route('search', ['q' => $query, 'filter' => 'chirps']) }}"
                            class="btn btn-sm {{ $filter === 'chirps' ? 'btn-primary' : 'btn-ghost' }}">
                            Chirps ({{ $chirps->total() ?? 0 }})
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Summary -->
        @if ($query)
            <div class="mt-6 text-sm text-base-content/60">
                @if (isset($message))
                    <p>{{ $message }}</p>
                @else
                    <p>
                        Found {{ $totalResults }} result{{ $totalResults !== 1 ? 's' : '' }}
                        for "{{ $query }}"
                        @if ($filter === 'chirps' && $chirps instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            (showing {{ $chirps->firstItem() }}-{{ $chirps->lastItem() }})
                        @endif
                    </p>
                @endif
            </div>
        @endif

        <!-- Users Results -->
        @if ($users->isNotEmpty() && in_array($filter, ['all', 'users']))
            <div class="mt-6">
                @if ($filter === 'all')
                    <h2 class="text-xl font-bold mb-4">Users</h2>
                @endif

                <div class="space-y-3">
                    @foreach ($users as $user)
                        <div class="card bg-base-100 hover:bg-base-200 transition-colors">
                            <div class="card-body p-4">
                                <a href="{{ route('profile.show', $user) }}" class="flex items-center gap-4">
                                    <div class="avatar">
                                        <div class="w-12 h-12 rounded-full">
                                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold truncate">{{ $user->name }}</h3>
                                        <p class="text-sm text-base-content/60 truncate">{{ $user->email }}</p>
                                        @if ($user->bio)
                                            <p class="text-sm text-base-content/80 mt-1 line-clamp-1">
                                                {{ $user->bio }}</p>
                                        @endif
                                    </div>
                                    <div class="badge badge-ghost badge-sm">
                                        {{ $user->chirps_count ?? $user->chirps()->count() }} chirps
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Chirps Results -->
        @if ($chirps->isNotEmpty() && in_array($filter, ['all', 'chirps']))
            <div class="mt-6">
                @if ($filter === 'all')
                    <h2 class="text-xl font-bold mb-4">Chirps</h2>
                @endif

                <div class="space-y-4">
                    @foreach ($chirps as $chirp)
                        <x-chirp :chirp="$chirp" />
                    @endforeach
                </div>

                {{-- Pagination for Chirps --}}
                @if ($chirps instanceof \Illuminate\Pagination\LengthAwarePaginator && $chirps->hasPages())
                    <div class="mt-6">
                        {{ $chirps->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- Empty State -->
        @if ($query && $users->isEmpty() && $chirps->isEmpty() && !isset($message))
            <div class="hero py-16">
                <div class="hero-content text-center">
                    <div>
                        <svg class="mx-auto h-16 w-16 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium">No results found</h3>
                        <p class="mt-2 text-base-content/60">Try adjusting your search terms or filters</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Initial State (no query) -->
        @if (!$query)
            <div class="hero py-16">
                <div class="hero-content text-center">
                    <div>
                        <svg class="mx-auto h-16 w-16 opacity-30" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium">Search Chirper</h3>
                        <p class="mt-2 text-base-content/60">Find users by name, email, or bio<br>Search chirps by
                            message content</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layout>
