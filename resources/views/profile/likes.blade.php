<x-layout>
    <x-slot:title>
        {{ $user->name }}'s Likes
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-4 mt-8 mb-4">
            <a href="{{ route('profile.show', $user) }}" class="btn btn-ghost btn-sm">
                ← Back to Profile
            </a>
        </div>

        <h1 class="text-2xl font-bold">Liked Chirps</h1>
        <p class="text-base-content/60">Chirps {{ $user->name }} has liked</p>

        <div class="space-y-4 mt-6">
            @forelse ($chirps as $chirp)
                <x-chirp :chirp="$chirp" />
            @empty
                <div class="card bg-base-100">
                    <div class="card-body text-center text-base-content/60">
                        <p>No liked chirps yet.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($chirps->hasPages())
            <div class="mt-6">
                {{ $chirps->links() }}
            </div>
        @endif
    </div>
</x-layout>
