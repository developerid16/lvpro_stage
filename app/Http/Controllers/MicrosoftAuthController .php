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

        $keys = Http::get(
            'https://login.microsoftonline.com/organizations/discovery/v2.0/keys'
        )->json();

        $decoded = JWT::decode($token, JWK::parseKeySet($keys));

        $user = User::updateOrCreate(
            ['email' => $decoded->preferred_username],
            ['name' => $decoded->name]
        );

        auth()->login($user);

        return response()->json(['success' => true]);
    }
}
