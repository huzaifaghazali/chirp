<x-layout>
    <x-slot:title>
        {{ $user->name }}'s Profile
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <!-- Profile Header -->
        <div class="card bg-base-100 shadow mt-8">
            <div class="card-body">
                <div class="flex items-start gap-6">
                    <!-- Avatar -->
                    <div class="avatar">
                        <div class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}'s avatar" />
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                                <p class="text-base-content/60 text-sm">{{ $user->email }}</p>
                            </div>
                            <div class='flex gap-2'>
                                @can('update', $user)
                                    <a href="{{ route('profile.edit', $user) }}" class="btn btn-ghost btn-sm">
                                        Edit Profile
                                    </a>
                                @else
                                    {{-- Add Report Button for other users --}}
                                    <x-report-button type="user" :id="$user->id" buttonClass="btn-ghost btn-sm" />
                                @endcan
                            </div>
                        </div>

                        @if ($user->bio)
                            <p class="mt-4 text-base-content/80">{{ $user->bio }}</p>
                        @endif

                        <!-- Stats -->
                        <div class="flex gap-6 mt-4 text-sm">
                            <div>
                                <span class="font-semibold">{{ $chirps->total() }}</span>
                                <span class="text-base-content/60">Chirps</span>
                            </div>
                            <div>
                                <a href="{{ route('profile.likes', $user) }}" class="hover:underline">
                                    <span class="font-semibold">{{ $user->likedChirps()->count() }}</span>
                                    <span class="text-base-content/60">Likes</span>
                                </a>
                            </div>
                            <div>
                                <span class="font-semibold">{{ $user->created_at->format('M Y') }}</span>
                                <span class="text-base-content/60">Joined</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User's Chirps -->
        <h2 class="text-xl font-bold mt-8 mb-4">Recent Chirps</h2>

        <div class="space-y-4">
            @forelse ($chirps as $chirp)
                <x-chirp :chirp="$chirp" />
            @empty
                <div class="card bg-base-100">
                    <div class="card-body text-center text-base-content/60">
                        <p>No chirps yet.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $chirps->links() }}
        </div>
    </div>
</x-layout>
{{--  <img src="https://avatars.laravel.cloud/{{ urlencode($user->email) }}?s=200"
                                alt="{{ $user->name }}'s avatar" /> --}}
