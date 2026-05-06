<?php

namespace App\Http\Controllers;

use App\Services\SocialAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(private readonly SocialAuthService $socialAuth) {}

    public function showLogin()
    {
        return view('auth.login');
    }

    // ── Google ───────────────────────────────────────────────────────────────

    public function redirectToGoogle(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->scopes(['openid', 'profile', 'email'])->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        $socialUser = Socialite::driver('google')->user();
        $user = $this->socialAuth->findOrCreateFromGoogle($socialUser);

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }

    // ── Facebook ─────────────────────────────────────────────────────────────

    public function redirectToFacebook(): RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('facebook');

        return $driver->setScopes(['public_profile', 'email', 'user_likes'])->redirect();
    }

    public function handleFacebookCallback(): RedirectResponse
    {
        $socialUser = Socialite::driver('facebook')->user();
        $user = $this->socialAuth->findOrCreateFromFacebook($socialUser);

        Auth::login($user, remember: true);

        return redirect()->route('dashboard');
    }

    // ── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        return view('dashboard', ['user' => Auth::user()]);
    }
}
