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
     * Test configuration endpoint (for debugging)
     */
    public function testConfig()
    {
        if (!app()->environment('local')) {
            return response()->json(['error' => 'Only available in development'], 403);
        }

        return response()->json([
            'linkedin' => [
                'client_id' => config('services.linkedin.client_id') ? 'SET (' . substr(config('services.linkedin.client_id'), 0, 8) . '...)' : 'MISSING',
                'client_secret' => config('services.linkedin.client_secret') ? 'SET' : 'MISSING',
                'redirect' => config('services.linkedin.redirect'),
            ],
            'google' => [
                'client_id' => config('services.google.client_id') ? 'SET (' . substr(config('services.google.client_id'), 0, 8) . '...)' : 'MISSING',
                'client_secret' => config('services.google.client_secret') ? 'SET' : 'MISSING',
                'redirect' => config('services.google.redirect'),
            ],
            'environment' => [
                'APP_ENV' => env('APP_ENV'),
                'LINKEDIN_CLIENT_ID' => env('LINKEDIN_CLIENT_ID') ? 'SET' : 'MISSING',
                'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID') ? 'SET' : 'MISSING',
            ]
        ]);
    }

    /**
     * Redirect to LinkedIn OAuth (Stateless - Production Safe)
     */
    public function redirectToLinkedIn(Request $request)
    {
        try {
            // Validate configuration
            $clientId = config('services.linkedin.client_id');
            $clientSecret = config('services.linkedin.client_secret');
            $redirectUri = config('services.linkedin.redirect');

            if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
                \Log::error('LinkedIn OAuth configuration incomplete', [
                    'client_id_set' => !empty($clientId),
                    'client_secret_set' => !empty($clientSecret),
                    'redirect_uri_set' => !empty($redirectUri),
                ]);

                throw new Exception('LinkedIn OAuth configuration is incomplete');
            }

            // Generate state for security
            $state = Str::random(40);

            // Build OAuth URL manually (stateless approach)
            $authUrl = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'scope' => 'r_liteprofile r_emailaddress'
            ]);

            \Log::info('LinkedIn OAuth redirect generated successfully');

            return response()->json([
                'success' => true,
                'redirect_url' => $authUrl,
                'state' => $state // Return state for frontend validation
            ]);

        } catch (Exception $e) {
            \Log::error('LinkedIn Redirect Error: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate LinkedIn redirect URL'
            ], 500);
        }
    }

    /**
     * Redirect to Google OAuth (Stateless - Production Safe)
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            // Validate configuration
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');
            $redirectUri = config('services.google.redirect');

            if (empty($clientId) || empty($clientSecret) || empty($redirectUri)) {
                \Log::error('Google OAuth configuration incomplete', [
                    'client_id_set' => !empty($clientId),
                    'client_secret_set' => !empty($clientSecret),
                    'redirect_uri_set' => !empty($redirectUri),
                ]);

                throw new Exception('Google OAuth configuration is incomplete');
            }

            // Generate state for security
            $state = Str::random(40);

            // Build OAuth URL manually (stateless approach)
            $authUrl = 'https://accounts.google.com/oauth/v2/auth?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'scope' => 'profile email',
                'access_type' => 'offline',
                'prompt' => 'consent'
            ]);

            \Log::info('Google OAuth redirect generated successfully');

            return response()->json([
                'success' => true,
                'redirect_url' => $authUrl,
                'state' => $state // Return state for frontend validation
            ]);

        } catch (Exception $e) {
            \Log::error('Google Redirect Error: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

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
            \Log::info("Processing {$provider} OAuth callback", [
                'has_code' => !empty($request->code),
                'has_state' => !empty($request->state),
            ]);

            // Get user from social provider using Socialite
            $socialUser = Socialite::driver($provider)->stateless()->user();

            if (!$socialUser) {
                \Log::error("Failed to retrieve user from {$provider}");
                return response()->json([
                    'success' => false,
                    'error' => "Failed to retrieve user from {$provider}"
                ], 400);
            }

            \Log::info("Successfully retrieved user from {$provider}", [
                'user_id' => $socialUser->getId(),
                'user_email' => $socialUser->getEmail(),
                'user_name' => $socialUser->getName(),
            ]);

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
                \Log::info("Existing user found for {$provider}", ['user_id' => $user->id]);
                // Update existing user
                $user = $this->updateExistingUser($user, $provider, $providerId, $firstName, $lastName, $avatar);
            } else {
                \Log::info("Creating new user for {$provider}");
                // Create new user
                $user = $this->createNewUser($email, $provider, $providerId, $firstName, $lastName, $name, $avatar);
            }

            // Generate token
            $token = $user->createToken('hiringjet_token')->plainTextToken;

            // Load user with profile relationship
            $user->load('user_profile');

            \Log::info("Successfully authenticated user via {$provider}", [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

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
                'error' => "An error occurred during {$provider} authentication",
                'debug' => app()->environment('local') ? $e->getMessage() : null
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
            \Log::info("Updated existing user", ['user_id' => $user->id, 'updates' => array_keys($updateData)]);
        }

        // Update profile picture if not set
        if ($user->user_profile && !$user->user_profile->profile_picture && $avatar) {
            $user->user_profile->update(['profile_picture' => $avatar]);
            \Log::info("Updated user profile picture", ['user_id' => $user->id]);
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

        \Log::info("Created new user from {$provider}", [
            'user_id' => $user->id,
            'email' => $user->email,
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
