<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the profile settings page.
     */
    public function index()
    {
        return view('profile.index');
    }

    /**
     * Update the user's profile photo.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old photo if it exists
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Store new photo
        $path = $request->file('profile_photo')->store('profile-photos', 'public');

        // Update user record
        $user->update([
            'profile_photo_path' => $path,
        ]);

        return redirect()->back()->with('success', 'Profile photo updated successfully.');
    }
}

