<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends BaseApiController
{
    /**
     * Redirect to LinkedIn OAuth
     */
    public function redirectToLinkedIn(Request $request)
    {
        try {
            $redirectUrl = Socialite::driver('linkedin')
                ->scopes(['r_liteprofile', 'r_emailaddress'])
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl
            ]);
        } catch (Exception $e) {
            \Log::error('LinkedIn Redirect Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate LinkedIn redirect URL'
            ], 500);
        }
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            $redirectUrl = Socialite::driver('google')
                ->scopes(['profile', 'email'])
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl
            ]);
        } catch (Exception $e) {
            \Log::error('Google Redirect Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate Google redirect URL'
            ], 500);
        }
    }

    /**
     * Handle LinkedIn OAuth callback
     */
    public function handleLinkedInCallback(Request $request)
    {
        return $this->handleSocialCallback('linkedin', $request);
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        return $this->handleSocialCallback('google', $request);
    }

    /**
     * Generic method to handle social OAuth callbacks
     */
    private function handleSocialCallback($provider, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'state' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request parameters',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            // Get user from social provider using Socialite
            $socialUser = Socialite::driver($provider)->stateless()->user();

            if (!$socialUser) {
                return response()->json([
                    'success' => false,
                    'error' => "Failed to retrieve user from {$provider}"
                ], 400);
            }

            // Extract user data
            $email = $socialUser->getEmail();
            $providerId = $socialUser->getId();
            $name = $socialUser->getName();
            $avatar = $socialUser->getAvatar();

            // Parse name into first and last name
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            // Check if user exists by email or provider ID
            $user = User::where('email', $email)
                       ->orWhere(function($query) use ($provider, $providerId) {
                           $query->where('provider', $provider)
                                 ->where('provider_id', $providerId);
                       })
                       ->first();

            if ($user) {
                // Update existing user
                $user = $this->updateExistingUser($user, $provider, $providerId, $firstName, $lastName, $avatar);
            } else {
                // Create new user
                $user = $this->createNewUser($email, $provider, $providerId, $firstName, $lastName, $name, $avatar);
            }

            // Generate token
            $token = $user->createToken('hiringjet_token')->plainTextToken;

            // Load user with profile relationship
            $user->load('user_profile');

            return response()->json([
                'success' => true,
                'message' => ucfirst($provider) . ' login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ]);

        } catch (Exception $e) {
            \Log::error(ucfirst($provider) . ' OAuth Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => "An error occurred during {$provider} authentication"
            ], 500);
        }
    }

    /**
     * Update existing user with social provider information
     */
    private function updateExistingUser($user, $provider, $providerId, $firstName, $lastName, $avatar)
    {
        $updateData = [];

        // Update provider-specific fields
        if ($provider === 'linkedin' && !$user->linkedin_id) {
            $updateData['linkedin_id'] = $providerId;
        } elseif ($provider === 'google' && !$user->google_id) {
            $updateData['google_id'] = $providerId;
        }

        // Update general fields if empty
        if (!$user->first_name && $firstName) {
            $updateData['first_name'] = $firstName;
        }

        if (!$user->last_name && $lastName) {
            $updateData['last_name'] = $lastName;
        }

        // Update primary provider if not set
        if (!$user->provider) {
            $updateData['provider'] = $provider;
            $updateData['provider_id'] = $providerId;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Update profile picture if not set
        if ($user->user_profile && !$user->user_profile->profile_picture && $avatar) {
            $user->user_profile->update(['profile_picture' => $avatar]);
        }

        return $user;
    }

    /**
     * Create new user from social provider data
     */
    private function createNewUser($email, $provider, $providerId, $firstName, $lastName, $name, $avatar)
    {
        $userData = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $name,
            'password' => Hash::make(Str::random(32)), // Random password
            'email_verified_at' => now(), // Social provider emails are verified
            'provider' => $provider,
            'provider_id' => $providerId,
        ];

        // Add provider-specific ID
        if ($provider === 'linkedin') {
            $userData['linkedin_id'] = $providerId;
        } elseif ($provider === 'google') {
            $userData['google_id'] = $providerId;
        }

        $user = User::create($userData);

        // Create user profile
        UserProfile::create([
            'user_id' => $user->id,
            'profile_picture' => $avatar,
            'completed_steps' => 1, // Set initial completion step
        ]);

        return $user;
    }

    /**
     * Get user profile data for debugging
     */
    public function getSocialUserData($provider)
    {
        try {
            $user = Socialite::driver($provider)->stateless()->user();

            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'nickname' => $user->getNickname(),
                'avatar' => $user->getAvatar(),
                'raw' => $user->getRaw()
            ];
        } catch (Exception $e) {
            \Log::error("Error fetching {$provider} user data: " . $e->getMessage());
            return null;
        }
    }
}
