<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ── Q1 helpers ──────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    // ── Google ───────────────────────────────────────────────────────────────

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        $socialUser = Socialite::driver('google')->user();

        $user = User::updateOrCreate(
            ['provider' => 'google', 'provider_id' => $socialUser->getId()],
            [
                'name'   => $socialUser->getName(),
                'email'  => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'token'  => $socialUser->token,
            ]
        );

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    // ── Facebook ─────────────────────────────────────────────────────────────

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')
            ->setScopes(['public_profile', 'email', 'user_likes'])
            ->redirect();
    }

    public function handleFacebookCallback()
    {
        $socialUser = Socialite::driver('facebook')->user();

        // Fetch liked pages from Graph API
        $likedPages = $this->fetchFacebookLikedPages($socialUser->token);

        $user = User::updateOrCreate(
            ['provider' => 'facebook', 'provider_id' => $socialUser->getId()],
            [
                'name'        => $socialUser->getName(),
                'email'       => $socialUser->getEmail() ?? $socialUser->getId() . '@facebook.com',
                'avatar'      => $socialUser->getAvatar(),
                'token'       => $socialUser->token,
                'liked_pages' => $likedPages,
            ]
        );

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }

    private function fetchFacebookLikedPages(string $token): array
    {
        try {
            $url      = "https://graph.facebook.com/me/likes?fields=name,category,picture&access_token={$token}";
            $response = file_get_contents($url);
            $data     = json_decode($response, true);

            return $data['data'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // ── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $user = Auth::user();

        return view('dashboard', compact('user'));
    }
}
