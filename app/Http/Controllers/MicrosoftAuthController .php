<?php
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MicrosoftAuthController extends Controller
{
    public function login(Request $request)
    {
        $token = $request->id_token;

        // Fetch tenant public keys
        $keys = Http::get('https://login.microsoftonline.com/' .config('services.azure.tenant_id') .'/discovery/v2.0/keys')->json();

        // Decode & verify token
        $decoded = JWT::decode($token, JWK::parseKeySet($keys));

        $email = $decoded->preferred_username ?? $decoded->email;

        // 🔍 Check if user already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // ✅ Existing user → login
            auth()->login($existingUser);
            return response()->json(['success' => true, 'redirect' => '/dashboard']);
        }

        $user = User::create([ 'email' => $email, 'name'  => $decoded->name ?? '', ]);

        return response()->json([
            'success'  => true,
            'redirect' => '/user-rights-form'
        ]);
    }

}
