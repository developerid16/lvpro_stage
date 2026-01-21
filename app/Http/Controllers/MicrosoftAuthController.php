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
    
        $code  = $request->query('code');
        $state = $request->query('state');

    
        if (!$code) {
            return redirect('/login')->withErrors('Authorization code missing');
        }
    
        if ($state !== session('azure_oauth_state')) {
            abort(403, 'Invalid OAuth state');
        }

    
        $tokenResponse = Http::asForm()->post(
            'https://login.microsoftonline.com/' .
            config('services.azure.tenant_id') .
            '/oauth2/v2.0/token',
            [
                'client_id'     => config('services.azure.client_id'),
                'client_secret' => config(key: 'services.azure.client_secret'),
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => config(key: 'services.azure.redirect_url'),
                'scope'         => 'openid profile email User.Read offline_access',
            ]
        );
    
        if (!$tokenResponse->successful()) {
            return redirect('/login')->withErrors('Microsoft login failed');
        }
    
        $accessToken = $tokenResponse->json('access_token');
    
        $profile = Http::withToken($accessToken)
            ->get('https://graph.microsoft.com/v1.0/me')
            ->json();

        $email = $profile['mail'] ?? $profile['userPrincipalName'] ?? null;
        
        
        $checkUserEmail = User::where('email', $email)->first();
        if($checkUserEmail == null){
            return redirect('/user-rights-form')->withErrors('User not found');
            }
            
            
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $profile['displayName'] ?? $email]
            );
        $user->last_login_at = now(); // or provider = 'microsoft'
        $user->login_type = 'microsoft'; // or provider = 'microsoft'
        $user->save();
    
        auth()->login($user);
        Auth::login($user);

        session()->forget('otp_verified');
        session(['otp_verified' => false]);
    
        return redirect('/dashboard');
    }


public function redirect()
{
    $state = bin2hex(random_bytes(16));
    session(['azure_oauth_state' => $state]);
 
    $query = http_build_query([
        'client_id'     => config('services.azure.client_id'),
        'response_type' => 'code',
        'response_mode' => 'query', //
        'redirect_uri'  => config('services.azure.redirect_url'),
        'scope'         => 'openid profile email User.Read offline_access',
        'state'         => $state,
    ]);
 
    return redirect(
        'https://login.microsoftonline.com/' .
        config('services.azure.tenant_id') .
        '/oauth2/v2.0/authorize?' . $query
    );
}


    }
