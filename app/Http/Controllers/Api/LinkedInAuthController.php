<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LinkedInAuthController extends BaseApiController
{
    public function redirect()
    {
        $redirectUrl = Socialite::driver('linkedin')->stateless()->redirect()->getTargetUrl();

        return response()->json(['url' => $redirectUrl]);
    }

    public function callback()
    {
        $linkedInUser = Socialite::driver('linkedin')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $linkedInUser->getEmail()]
        );

        // You can use Sanctum, JWT, or a simple token here
        $token = $user->createToken('linkedin-token')->plainTextToken;

        // return redirect()->away("http://localhost:3000/linkedin/callback?token={$token}");
         return response()->json(['token' => $token, 'user' => $user]);
    }
}
