<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LinkedInAuthController extends BaseApiController
{
    public function redirect()
    {
        $redirectUrl = Socialite::driver('linkedin')->stateless()->redirect()->getTargetUrl();

        return response()->json(['url' => $redirectUrl]);
    }

    public function callback_bkp()
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

    public function callback(Request $request){
        $code = $request->input('code');
        $redirectUri = env('LINKEDIN_REDIRECT_URI');

        if (!$code) {
            return response()->json(['error' => 'Authorization code missing'], 400);
        }

        // Exchange authorization code for access token
        $response = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => env('LINKEDIN_CLIENT_ID'),
            'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to obtain access token'], 500);
        }

        $accessToken = $response->json()['access_token'];

        // Fetch user profile
        $profile = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/me');
        if ($profile->failed()) {
            return response()->json(['error' => 'Failed to fetch user profile'], 500);
        }

        // Return user data
        return response()->json($profile->json());
    }

    //https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=86g1b1a3lmmpgj&redirect_uri=http://localhost:9105/api/linkedin/callback&scope=r_liteprofile%20r_emailaddress&state=abc123

}
