@props(['chirp'])

<div class="card bg-base-100" data-chirp-id="{{ $chirp->id }}">
    <div class="card-body">
        <div class="flex space-x-3">
            @if ($chirp->user)
                <div class="avatar">
                    <div class="size-10 rounded-full">
                        <a href="{{ route('profile.show', $chirp->user) }}">
                            <img src="{{ $chirp->user->avatar_url }}" alt="{{ $chirp->user->name }}'s avatar"
                                class="rounded-full hover:opacity-80 transition-opacity" />
                        </a>
                    </div>
                </div>
            @else
                <div class="avatar placeholder">
                    <div class="size-10 rounded-full">
                        <img src="https://avatars.laravel.cloud/f61123d5-0b27-434c-a4ae-c653c7fc9ed6?vibe=stealth"
                            alt="Anonymous User" class="rounded-full" />
                    </div>
                </div>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex justify-between w-full">
                    <div class="flex items-center gap-1">
                        @if ($chirp->user)
                            <a href="{{ route('profile.show', $chirp->user) }}"
                                class="text-sm font-semibold hover:underline">
                                {{ $chirp->user->name }}
                            </a>
                        @else
                            <span class="text-sm font-semibold">Anonymous</span>
                        @endif
                        <span class="text-base-content/60">·</span>
                        <span class="text-sm text-base-content/60">{{ $chirp->created_at->diffForHumans() }}</span>
                        @if ($chirp->updated_at->gt($chirp->created_at->addSeconds(5)))
                            <span class="text-base-content/60">·</span>
                            <span class="text-sm text-base-content/60 italic">edited</span>
                        @endif
                    </div>

                    @can('update', $chirp)
                        <div class="flex gap-1">
                            <a href="/chirps/{{ $chirp->id }}/edit" class="btn btn-ghost btn-xs">
                                Edit
                            </a>
                            <form method="POST" action="/chirps/{{ $chirp->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this chirp?')"
                                    class="btn btn-ghost btn-xs text-error">
                                    Delete
                                </button>
                            </form>
                        </div>
                    @endcan
                </div>

                <p class="mt-1">{{ $chirp->message }}</p>

                {{-- Like Button --}}
                <div class="mt-3 flex items-center gap-4">
                    @auth
                        <button
                            class="btn btn-ghost btn-xs gap-1 like-btn {{ $chirp->is_liked_by_current_user ? 'text-error' : 'text-base-content/60' }}"
                            data-liked="{{ $chirp->is_liked_by_current_user ? 'true' : 'false' }}"
                            onclick="toggleLike({{ $chirp->id }}, this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 like-icon transition-transform"
                                fill="{{ $chirp->is_liked_by_current_user ? 'currentColor' : 'none' }}" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span class="likes-count">{{ $chirp->likes_count }}</span>
                        </button>
                        {{-- Add Report Button --}}
                        <x-report-button type="chirp" :id="$chirp->id" buttonClass="btn-ghost" />
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-xs gap-1 text-base-content/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            <span>{{ $chirp->likes_count }}</span>
                        </a>
                        {{-- Guest Report Link --}}
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-xs gap-1 text-base-content/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Report</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
