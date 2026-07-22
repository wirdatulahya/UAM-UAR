<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle the login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required'    => 'Username or email is required.',
            'password.required' => 'Password is required.',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField  => $request->login,
            'password'   => $request->password,
        ];

        if (Auth::validate($credentials)) {
            $user = \App\Models\User::where($loginField, $request->login)->first();

            if ($user && $user->account_status === 'Inactive') {
                return back()
                    ->withInput($request->only('login'))
                    ->withErrors([
                        'login' => 'Your account has been deactivated. Please contact the system administrator.',
                    ]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors([
                'login' => 'Invalid username or password. Please check your credentials and try again.',
            ]);
    }

    /**
     * Handle the logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
