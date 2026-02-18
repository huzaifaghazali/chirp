<x-layout>
    <x-slot:title>
        Edit Profile
    </x-slot:title>

    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mt-8">Edit Profile</h1>

        <div class="card bg-base-100 mt-8">
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update', $user) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Avatar Upload Section -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text font-semibold">Profile Picture</span>
                        </label>
                        <div class="flex items-center gap-6">
                            <!-- Current Avatar Preview -->
                            <div class="avatar">
                                <div
                                    class="w-24 h-24 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                    <img id="avatar-preview" src="{{ $user->avatar_url }}"
                                        alt="{{ $user->name }}'s avatar" />
                                </div>
                            </div>
                            <div class="flex-1 space-y-3">

                                <!-- File Input -->
                                <input type="file" name="avatar" id="avatar-input" accept="image/*"
                                    class="file-input file-input-bordered w-full @error('avatar') file-input-error @enderror" />
                                @if ($user->avatar)
                                    <!-- Remove Avatar Option -->
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="remove_avatar" value="1"
                                            class="checkbox checkbox-sm" />
                                        <span class="text-sm text-error">Remove current avatar</span>

                                    </label>
                                @endif
                                <p class="text-xs text-base-content/60">
                                    JPG, PNG, GIF up to 2MB. Max 2000x2000px.
                                </p>
                            </div>
                        </div>
                        @error('avatar')
                            <label class="label">

                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="input input-bordered w-full @error('name') input-error @enderror" required>
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="input input-bordered w-full @error('email') input-error @enderror" required>
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Bio -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Bio</span>
                        </label>
                        <textarea name="bio" rows="3" maxlength="500"
                            class="textarea textarea-bordered w-full resize-none @error('bio') textarea-error @enderror"
                            placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                        <label class="label">
                            <span class="label-text-alt">Max 500 characters</span>
                        </label>
                        @error('bio')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <div class="divider">Change Password</div>

                    <!-- Current Password -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">Current Password</span>
                            <span class="label-text-alt">Required only if changing password</span>
                        </label>
                        <input type="password" name="current_password"
                            class="input input-bordered w-full @error('current_password') input-error @enderror">
                        @error('current_password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="form-control w-full mb-4">
                        <label class="label">
                            <span class="label-text">New Password</span>
                        </label>
                        <input type="password" name="password"
                            class="input input-bordered w-full @error('password') input-error @enderror">
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-control w-full mb-6">
                        <label class="label">
                            <span class="label-text">Confirm New Password</span>
                        </label>
                        <input type="password" name="password_confirmation" class="input input-bordered w-full">
                    </div>

                    <!-- Actions -->
                    <div class="card-actions justify-between">
                        <a href="{{ route('profile.show', $user) }}" class="btn btn-ghost btn-sm">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- JavaScript for live preview -->

    <script>
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;

                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</x-layout>
