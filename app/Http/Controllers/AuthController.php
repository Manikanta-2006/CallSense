<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle credential-based login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Track login activity
            LoginActivity::create([
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'action'     => 'login',
                'login_time' => now(),
            ]);

            return redirect()->intended('/calls');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle credential-based registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Track login activity
        LoginActivity::create([
            'user_id'    => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'action'     => 'login', // Track registration as login
            'login_time' => now(),
        ]);

        return redirect('/calls');
    }
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update their google_id and avatar
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            } else {
                // Create a new user
                $user = User::create([
                    'name'      => $googleUser->getName(),
                    'email'     => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                    'password'  => null // Nullable via the updated migration
                ]);
            }

            Auth::login($user);

            // Track login activity
            LoginActivity::create([
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'action'     => 'login',
                'login_time' => now(),
            ]);

            return redirect()->intended('/calls');
        } catch (\Exception $e) {
            return redirect('/')->withErrors(['msg' => 'An error occurred during authentication: ' . $e->getMessage()]);
        }
    }
}
