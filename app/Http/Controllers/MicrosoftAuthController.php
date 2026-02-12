<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MicrosoftAuthController extends Controller
{
    public function login(Request $request)
    {
        // ===== STEP 1: LOG EVERYTHING FOR DEBUGGING =====
        Log::info('Microsoft Callback Hit', [
            'all_input' => $request->all(),
            'full_url' => $request->fullUrl(),
            'query_string' => $request->getQueryString(),
        ]);

        // ===== STEP 2: CHECK FOR ERRORS FIRST (before anything else) =====
        if ($request->has('error')) {
            $error = $request->input('error');
            $errorDescription = $request->input('error_description', '');
            $errorUri = $request->input('error_uri', '');
            
            Log::error('Microsoft OAuth Error Detected', [
                'error' => $error,
                'error_description' => $errorDescription,
                'error_uri' => $errorUri,
            ]);

            // Parse the error description to make it user-friendly
            $userMessage = $this->parseErrorMessage($error, $errorDescription);

            return redirect()->route('login')
                ->withErrors(['microsoft' => $userMessage])
                ->withInput();
        }

        // ===== STEP 3: VALIDATE CODE =====
        $code = $request->input('code');
        if (!$code) {
            Log::warning('No authorization code received');
            return redirect()->route('login')
                ->withErrors(['microsoft' => 'No authorization code received from Microsoft']);
        }

        // ===== STEP 4: VALIDATE STATE =====
        $state = $request->input('state');
        $sessionState = session('azure_oauth_state');
        
        Log::info('State Validation', [
            'received_state' => $state,
            'session_state' => $sessionState,
            'match' => $state === $sessionState
        ]);

        if ($state !== $sessionState) {
            session()->forget('azure_oauth_state');
            return redirect()->route('login')
                ->withErrors(['microsoft' => 'Security validation failed. Please try again.']);
        }

        session()->forget('azure_oauth_state');

        // ===== STEP 5: EXCHANGE CODE FOR TOKEN =====
        try {
            $tokenUrl = 'https://login.microsoftonline.com/' . 
                        config('services.azure.tenant_id') . 
                        '/oauth2/v2.0/token';

            Log::info('Requesting token from', ['url' => $tokenUrl]);

            $tokenResponse = Http::asForm()->post($tokenUrl, [
                'client_id'     => config('services.azure.client_id'),
                'client_secret' => config('services.azure.client_secret'),
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => config('services.azure.redirect_url'),
                'scope'         => 'openid profile email User.Read',
            ]);

            if (!$tokenResponse->successful()) {
                Log::error('Token exchange failed', [
                    'status' => $tokenResponse->status(),
                    'response' => $tokenResponse->json()
                ]);

                $errorData = $tokenResponse->json();
                $errorMsg = $errorData['error_description'] ?? 'Failed to authenticate with Microsoft';
                
                return redirect()->route('login')
                    ->withErrors(['microsoft' => $errorMsg]);
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'];

            Log::info('Access token received successfully');

        } catch (\Exception $e) {
            Log::error('Token exchange exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->withErrors(['microsoft' => 'An error occurred during authentication. Please try again.']);
        }

        // ===== STEP 6: GET USER PROFILE =====
        try {
            $profileResponse = Http::withToken($accessToken)
                ->get('https://graph.microsoft.com/v1.0/me');

            if (!$profileResponse->successful()) {
                Log::error('Profile fetch failed', [
                    'status' => $profileResponse->status(),
                    'response' => $profileResponse->json()
                ]);

                return redirect()->route('login')
                    ->withErrors(['microsoft' => 'Failed to retrieve your profile from Microsoft']);
            }

            $profile = $profileResponse->json();
            
            Log::info('User profile retrieved', [
                'display_name' => $profile['displayName'] ?? 'N/A',
                'email' => $profile['mail'] ?? $profile['userPrincipalName'] ?? 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('Profile fetch exception', [
                'message' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->withErrors(['microsoft' => 'Failed to retrieve your profile']);
        }

        // ===== STEP 7: EXTRACT AND VALIDATE EMAIL =====
        $email = $profile['mail'] ?? $profile['userPrincipalName'] ?? null;
        
        if (!$email) {
            Log::warning('No email found in profile', ['profile' => $profile]);
            return redirect()->route('login')
                ->withErrors(['microsoft' => 'Could not retrieve email from your Microsoft account']);
        }

        // ===== STEP 8: CHECK IF USER EXISTS =====
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            Log::info('User not found in database', ['email' => $email]);
            
            return redirect('/user-rights-form')
                ->with('pending_email', $email)
                ->with('pending_name', $profile['displayName'] ?? '')
                ->withErrors(['access' => 'Your account was not found. Please request access.']);
        }

        // ===== STEP 9: UPDATE USER AND LOGIN =====
        $user->update([
            'name' => $profile['displayName'] ?? $user->name,
            'last_login_at' => now(),
            'login_type' => 'microsoft',
        ]);

        Auth::login($user);

        session(['otp_verified' => false]);

        Log::info('User logged in successfully', ['user_id' => $user->id]);

        return redirect()->intended('/dashboard');
    }

    public function redirect()
    {
        $state = bin2hex(random_bytes(16));
        session(['azure_oauth_state' => $state]);

        Log::info('Initiating Microsoft OAuth', [
            'state' => $state,
            'redirect_uri' => config('services.azure.redirect_url')
        ]);

        $params = [
            'client_id'     => config('services.azure.client_id'),
            'response_type' => 'code',
            'response_mode' => 'query',
            'redirect_uri'  => config('services.azure.redirect_url'),
            'scope'         => 'openid profile email User.Read',
            'state'         => $state,
            'prompt'        => 'select_account',
        ];

        $query = http_build_query($params);

        $authorizeUrl = 'https://login.microsoftonline.com/' .
                       config('services.azure.tenant_id') .
                       '/oauth2/v2.0/authorize?' . $query;

        return redirect($authorizeUrl);
    }

    /**
     * Parse Microsoft error messages into user-friendly format
     */
    private function parseErrorMessage(string $error, string $errorDescription): string
    {
        // Check for AADSTS error codes
        if (str_contains($errorDescription, 'AADSTS50020')) {
            preg_match("/'([^']+@[^']+)'/", $errorDescription, $matches);
            $email = $matches[1] ?? 'your account';
            
            return "The account '{$email}' does not exist in the required Microsoft tenant. " .
                   "Please contact your IT administrator to add your account as an external user, " .
                   "or try signing in with a different Microsoft account.";
        }

        if (str_contains($errorDescription, 'AADSTS')) {
            preg_match('/AADSTS\d+/', $errorDescription, $matches);
            $errorCode = $matches[0] ?? '';
            return "Microsoft authentication error ({$errorCode}). Please contact support with this error code.";
        }

        // Handle common OAuth errors
        return match ($error) {
            'access_denied' => 'You cancelled the Microsoft sign-in process.',
            'invalid_scope' => 'Invalid permissions requested from Microsoft.',
            'invalid_request' => 'Invalid authentication request sent to Microsoft.',
            'unauthorized_client' => 'This application is not authorized for Microsoft sign-in.',
            'server_error' => 'Microsoft authentication server encountered an error. Please try again.',
            'temporarily_unavailable' => 'Microsoft authentication is temporarily unavailable. Please try again later.',
            default => "Microsoft authentication failed: {$error}"
        };
    }
}