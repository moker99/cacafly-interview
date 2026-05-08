<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Contracts\User as SocialUser;

class SocialAuthService
{
    public function findOrCreateFromGoogle(SocialUser $socialUser): User
    {
        return User::updateOrCreate(
            ['provider' => 'google', 'provider_id' => $socialUser->getId()],
            [
                'name'   => $socialUser->getName(),
                'email'  => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'token'  => $socialUser->token,
            ]
        );
    }

    public function findOrCreateFromFacebook(SocialUser $socialUser): User
    {
        return User::updateOrCreate(
            ['provider' => 'facebook', 'provider_id' => $socialUser->getId()],
            [
                'name'        => $socialUser->getName(),
                'email'       => $socialUser->getEmail() ?? $socialUser->getId() . '@facebook.com',
                'avatar'      => $socialUser->getAvatar(),
                'token'       => $socialUser->token,
                'liked_pages' => $this->fetchLikedPages($socialUser->token),
            ]
        );
    }

    public function fetchLikedPages(string $token): array
    {
        $response = Http::get('https://graph.facebook.com/me/likes', [
            'fields'       => 'name,category,picture',
            'access_token' => $token,
        ]);

        return $response->successful() ? ($response->json('data') ?? []) : [];
    }
}
