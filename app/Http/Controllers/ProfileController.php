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
        $user = Auth::user();

        // Calculate Activity Summary
        $requestsSubmitted = \App\Models\UamRequest::where('requested_by', $user->id)->count();
        $requestsApproved = \App\Models\UamApprovalHistory::where('user_id', $user->id)->where('status', 'Approved')->count();
        $requestsReturned = \App\Models\UamApprovalHistory::where('user_id', $user->id)->where('status', 'Return')->count();

        // Last activity from approval history or request creation
        $lastApprovalActivity = \App\Models\UamApprovalHistory::where('user_id', $user->id)->max('created_at');
        $lastRequestActivity = \App\Models\UamRequest::where('requested_by', $user->id)->max('created_at');
        $lastActivity = max($lastApprovalActivity, $lastRequestActivity);

        return view('profile.index', compact(
            'requestsSubmitted',
            'requestsApproved',
            'requestsReturned',
            'lastActivity'
        ));
    }

    /**
     * Update the user's profile information and password.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'division' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        
        $updates = [
            'name' => $request->name,
            'nik' => $request->nik,
            'department' => $request->department,
            'division' => $request->division,
            'position' => $request->position,
            'phone_number' => $request->phone_number,
        ];

        $user->update($updates);
        $user->checkOnboardingStatus();

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password from the profile page.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->password_changed_at = now();
        $user->save();
        $user->checkOnboardingStatus();

        return redirect()->back()->with('success', 'Password updated successfully.');
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

