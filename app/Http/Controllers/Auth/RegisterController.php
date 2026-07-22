<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle the registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'username'              => ['required', 'string', 'max:255', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'confirmed', Password::min(8)],
            'role'                  => ['required', 'string', 'in:admin,pic_ao,manager,ao'],
        ], [
            'name.required'         => 'Full name is required.',
            'username.required'     => 'Username is required.',
            'username.unique'       => 'This username is already taken.',
            'username.regex'        => 'Username may only contain letters, numbers, and underscores.',
            'email.required'        => 'Email address is required.',
            'email.unique'          => 'This email address is already registered.',
            'password.required'     => 'Password is required.',
            'password.confirmed'    => 'Password confirmation does not match.',
            'password.min'          => 'Password must be at least 8 characters.',
            'role.required'         => 'Role is required.',
            'role.in'               => 'Invalid role selected.',
        ]);

        User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return redirect()->route('login')
            ->with('success', 'Account registered successfully. Please log in.');
    }
}
