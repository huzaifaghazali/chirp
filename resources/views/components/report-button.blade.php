@props(['type', 'id', 'buttonClass' => 'btn-ghost'])

@auth
    @php
        $hasReported = \App\Models\Report::where(
            'reportable_type',
            $type === 'chirp' ? 'App\\Models\\Chirp' : 'App\\Models\\User',
        )
            ->where('reportable_id', $id)
            ->where('reporter_id', auth()->id())
            ->whereIn('status', ['pending', 'under_review'])
            ->exists();
    @endphp

    @if ($hasReported)
        <button class="btn {{ $buttonClass }} btn-xs gap-1" disabled title="You have already reported this">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>Reported</span>
        </button>
    @else
        <button onclick="reportModal{{ $type }}{{ $id }}.showModal()"
            class="btn {{ $buttonClass }} btn-xs gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>Report</span>
        </button>

        <dialog id="reportModal{{ $type }}{{ $id }}" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Report {{ $type === 'chirp' ? 'Chirp' : 'User' }}</h3>
                <p class="text-sm text-base-content/60 mt-2">Help us keep our community safe by reporting inappropriate
                    content.</p>

                <form method="POST" action="{{ route('reports.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="id" value="{{ $id }}">

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-semibold">Reason for reporting</span>
                        </label>
                        <div class="space-y-2">
                            <label
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="reason" value="spam" class="radio radio-primary" required>
                                <div>
                                    <div class="font-medium">Spam</div>
                                    <div class="text-xs text-base-content/60">Unwanted commercial content or repetitive
                                        posts</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="reason" value="harassment" class="radio radio-primary">
                                <div>
                                    <div class="font-medium">Harassment</div>
                                    <div class="text-xs text-base-content/60">Bullying, threats, or targeted abuse</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="reason" value="misinformation" class="radio radio-primary">
                                <div>
                                    <div class="font-medium">Misinformation</div>
                                    <div class="text-xs text-base-content/60">False or misleading information</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="reason" value="hate_speech" class="radio radio-primary">
                                <div>
                                    <div class="font-medium">Hate Speech</div>
                                    <div class="text-xs text-base-content/60">Content that promotes hatred or violence</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="reason" value="violence" class="radio radio-primary">
                                <div>
                                    <div class="font-medium">Violence</div>
                                    <div class="text-xs text-base-content/60">Graphic violence or dangerous content</div>
                                </div>
                            </label>
                            <label
                                class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-base-200 transition-colors">
                                <input type="radio" name="reason" value="other" class="radio radio-primary">
                                <div>
                                    <div class="font-medium">Other</div>
                                    <div class="text-xs text-base-content/60">Something else not covered above</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Additional details (optional)</span>
                        </label>
                        <textarea name="details" rows="3" class="textarea textarea-bordered resize-none"
                            placeholder="Please provide any additional context that might help our moderators..."></textarea>
                        <label class="label">
                            <span class="label-text-alt">Max 500 characters</span>
                        </label>
                    </div>

                    <div class="modal-action">
                        <button type="button" onclick="reportModal{{ $type }}{{ $id }}.close()"
                            class="btn btn-ghost">Cancel</button>
                        <button type="submit" class="btn btn-error">Submit Report</button>
                    </div>
                </form>
            </div>
        </dialog>
    @endif
@else
    <a href="{{ route('login') }}" class="btn {{ $buttonClass }} btn-xs gap-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span>Report</span>
    </a>
@endauth
