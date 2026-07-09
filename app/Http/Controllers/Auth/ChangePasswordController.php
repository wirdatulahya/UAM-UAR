<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    /**
     * Show the change password form.
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Handle the change password request.
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        // Update the user's password using Hash::make()
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully.');
    }
}
