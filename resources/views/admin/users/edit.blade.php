@extends('admin.layout')

@section('title', 'Edit User')

@section('content')
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">← Back to User</a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="card bg-base-100">
            <div class="card-body">
                <h1 class="text-2xl font-bold mb-6">Edit User: {{ $user->name }}</h1>

                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <!-- Avatar -->
                    <div class="flex items-center gap-4 mb-6">
                        <div class="avatar">
                            <div class="w-20 h-20 rounded-full">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                            </div>
                        </div>
                        <div>
                            <p class="font-semibold">{{ $user->name }}</p>
                            <p class="text-sm text-base-content/60">{{ $user->email }}</p>
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="input input-bordered @error('name') input-error @enderror" required />
                        @error('name')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="input input-bordered @error('email') input-error @enderror" required />
                        @error('email')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <!-- Bio -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Bio</span>
                        </label>
                        <textarea name="bio" rows="3" maxlength="500" class="textarea textarea-bordered resize-none">{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    <div class="divider">Admin Settings</div>

                    <!-- Admin Status -->
                    <div class="form-control mb-4">
                        <label class="label cursor-pointer justify-start gap-4">
                            <input type="checkbox" name="is_admin" value="1" class="checkbox checkbox-primary"
                                {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} />
                            <div>
                                <span class="label-text font-semibold">Administrator</span>
                                <p class="text-xs text-base-content/60">Grant admin panel access</p>
                            </div>
                        </label>
                    </div>

                    <!-- Account Status -->
                    <div class="form-control mb-4">
                        <label class="label">
                            <span class="label-text">Account Status</span>
                        </label>
                        <select name="status" class="select select-bordered">
                            <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active
                            </option>
                            <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>
                                Suspended</option>
                            <option value="banned" {{ old('status', $user->status) === 'banned' ? 'selected' : '' }}>Banned
                            </option>
                        </select>
                    </div>

                    <!-- Reason for changes -->
                    <div class="form-control mb-6">
                        <label class="label">
                            <span class="label-text">Reason for Changes (required for admin actions)</span>
                        </label>
                        <textarea name="reason" rows="2" class="textarea textarea-bordered" required
                            placeholder="e.g., Updated user information, granted admin privileges..."></textarea>
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
