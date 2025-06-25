<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController as BaseApiController;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
     * Redirect to LinkedIn OpenID Connect (Updated for OpenID Connect)
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

            // Build OpenID Connect URL (Updated scopes and endpoint)
            $authUrl = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'scope' => 'openid profile email' // OpenID Connect scopes
            ]);

            \Log::info('LinkedIn OpenID Connect redirect generated successfully', [
                'scope' => 'openid profile email',
                'protocol' => 'OpenID Connect'
            ]);

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
     * Redirect to Google OAuth (FIXED - Use Socialite consistently)
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

            // FIXED: Use Socialite for consistency
            $redirectUrl = Socialite::driver('google')
                ->stateless()
                ->with([
                    'state' => $state,
                    'access_type' => 'offline',
                    'prompt' => 'consent'
                ])
                ->redirect()
                ->getTargetUrl();

            \Log::info('Google OAuth redirect generated successfully', [
                'redirect_url' => $redirectUrl,
                'state' => $state
            ]);

            return response()->json([
                'success' => true,
                'redirect_url' => $redirectUrl,
                'state' => $state // Return state for frontend validation
            ]);

        } catch (Exception $e) {
            \Log::error('Google Redirect Error: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate Google redirect URL',
                'debug' => app()->environment('local') ? $e->getMessage() : null
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
                'protocol' => $provider === 'linkedin' ? 'OpenID Connect' : 'OAuth 2.0'
            ]);

            // Handle LinkedIn with OpenID Connect, Google with Socialite
            if ($provider === 'linkedin') {
                $socialUser = $this->getLinkedInUserViaOpenID($request->code);
            } else {
                // FIXED: Use Socialite for Google (consistent with redirect)
                $socialUser = $this->getGoogleUserViaSocialite($request->code);
            }

            if (!$socialUser) {
                \Log::error("Failed to retrieve user from {$provider}");
                return response()->json([
                    'success' => false,
                    'error' => "Failed to retrieve user from {$provider}"
                ], 400);
            }

            \Log::info("Successfully retrieved user from {$provider}", [
                'user_id' => $socialUser['id'],
                'user_email' => $socialUser['email'],
                'user_name' => $socialUser['name'],
            ]);

            // Extract user data (normalized format)
            $email = $socialUser['email'];
            $providerId = $socialUser['id'];
            $name = $socialUser['name'];
            $firstName = $socialUser['first_name'];
            $lastName = $socialUser['last_name'];
            $avatar = $socialUser['avatar'] ?? null;

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
            $token = JWTAuth::fromUser($user);
            // Set guard to "api" for the current request
            auth()->setUser($user);

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
     * FIXED: Get Google user data via Socialite (consistent approach)
     */
    private function getGoogleUserViaSocialite($code)
    {
        try {
            \Log::info('Starting Google OAuth flow via Socialite');

            // Use Socialite to get user (consistent with redirect)
            $googleUser = Socialite::driver('google')->stateless()->user();

            \Log::info('Google user data retrieved via Socialite', [
                'user_id' => $googleUser->getId(),
                'user_email' => $googleUser->getEmail()
            ]);

            // Normalize data format to match LinkedIn structure
            return [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'first_name' => $googleUser->user['given_name'] ?? '',
                'last_name' => $googleUser->user['family_name'] ?? '',
                'avatar' => $googleUser->getAvatar(),
                'email_verified' => $googleUser->user['email_verified'] ?? true,
                'raw' => $googleUser->getRaw()
            ];

        } catch (Exception $e) {
            \Log::error('Google OAuth via Socialite error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get LinkedIn user data via OpenID Connect
     */
    private function getLinkedInUserViaOpenID($code)
    {
        try {
            \Log::info('Starting LinkedIn OpenID Connect flow');

            // Step 1: Exchange code for access token
            $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('services.linkedin.redirect'),
                'client_id' => config('services.linkedin.client_id'),
                'client_secret' => config('services.linkedin.client_secret'),
            ]);

            if (!$tokenResponse->successful()) {
                \Log::error('LinkedIn token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'response' => $tokenResponse->body()
                ]);
                throw new Exception('Failed to exchange code for access token');
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];

            \Log::info('LinkedIn access token obtained successfully');

            // Step 2: Get user info using OpenID Connect userinfo endpoint
            $userResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://api.linkedin.com/v2/userinfo');

            if (!$userResponse->successful()) {
                \Log::error('LinkedIn userinfo request failed', [
                    'status' => $userResponse->status(),
                    'response' => $userResponse->body()
                ]);
                throw new Exception('Failed to get user info from LinkedIn');
            }

            $userData = $userResponse->json();

            \Log::info('LinkedIn user data retrieved', [
                'fields' => array_keys($userData)
            ]);

            // Step 3: Normalize data format (OpenID Connect fields)
            return [
                'id' => $userData['sub'], // 'sub' is the standard OpenID Connect user ID
                'email' => $userData['email'],
                'name' => $userData['name'],
                'first_name' => $userData['given_name'] ?? '',
                'last_name' => $userData['family_name'] ?? '',
                'avatar' => $userData['picture'] ?? null,
                'email_verified' => $userData['email_verified'] ?? false,
                'locale' => $userData['locale'] ?? null,
                'raw' => $userData // Store raw data for debugging
            ];

        } catch (Exception $e) {
            \Log::error('LinkedIn OpenID Connect error: ' . $e->getMessage());
            throw $e;
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
            'role_id'=> env('JOB_SEEKER_ROLE_ID'),
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => Hash::make(Str::random(32)), // Random password
            'email_verified_at' => now(), // Social provider emails are verified
            'provider' => $provider,
            'provider_id' => $providerId,
            'country_code' => '+971',
            'phone' => date('ymdhis'),
        ];

        // Add provider-specific ID
        if ($provider === 'linkedin') {
            $userData['linkedin_id'] = $providerId;
        } elseif ($provider === 'google') {
            $userData['google_id'] = $providerId;
        }

        \Log::info("createNewUser:Creating new user for {$provider} with data:", $userData);
        $user = User::create($userData);

        $this->calculate_profile_completed_percentage($user->id, 'full-name'); //Full name completes

        // Create user profile
        UserProfile::create([
            'user_id' => $user->id,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country_code' => '+971',
            'phone' => date('ymdhis'),
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
            if ($provider === 'linkedin') {
                // For LinkedIn, we'd need a code to test, so return config info instead
                return [
                    'provider' => 'linkedin',
                    'protocol' => 'OpenID Connect',
                    'endpoint' => 'https://api.linkedin.com/v2/userinfo',
                    'scopes' => 'openid profile email'
                ];
            } else {
                $user = Socialite::driver($provider)->stateless()->user();
                return [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'name' => $user->getName(),
                    'nickname' => $user->getNickname(),
                    'avatar' => $user->getAvatar(),
                    'raw' => $user->getRaw()
                ];
            }
        } catch (Exception $e) {
            \Log::error("Error fetching {$provider} user data: " . $e->getMessage());
            return null;
        }
    }
}
