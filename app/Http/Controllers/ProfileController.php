<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    // Display the specified users's profile
    public function show(User $user)
    {
        $chirps = $user->chirps()->latest()->paginate(10);
        $chirps = $user->chirps()
            ->with(['user', 'likes'])
            ->withCount('likes')
            ->latest()
            ->paginate(10);

        return view('profile.show', [
            'user' => $user,
            'chirps' => $chirps,
        ]);
    }

    /**
     * Show user's liked chirps
     */
    public function likes(User $user)
    {
        $chirps = $user->likedChirps()
            ->with(['user', 'likes'])
            ->withCount('likes')
            ->latest()
            ->paginate(10);

        return view('profile.likes', [
            'user' => $user,
            'chirps' => $chirps,
        ]);
    }

    // Show edit form (only for own profile)
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('profile.edit', compact('user'));
    }

    // Update the authenticated user's profile.
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'bio' => 'nullable|string|max:500',
            'avatar' => [
                'nullable',
                File::image()
                    ->min(1)
                    ->max(2048) // 2MB max
                    ->dimensions(Rule::dimensions()->maxWidth(2000)->maxHeight(2000))
            ],
            'remove_avatar' => 'nullable|boolean',
            'current_password' => 'required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Handle avatar removal
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
                $user->avatar = null;
            }
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar in avatars directory with original filename hashed
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        // Update basic info
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->bio = $validated['bio'] ?? null;

        // Update password if provided
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile.show', $user)->with('success', 'Profile updated successfully.');
    }
}
