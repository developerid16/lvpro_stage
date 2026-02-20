<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GUIDService
{
    private function getAzureToken(): string
    {
        $res = Http::asForm()->post(
            config('services.azure.token_url'),
            [
                'grant_type'    => 'client_credentials',
                'client_id'     => config('services.azure.client_id'),
                'client_secret' => config('services.azure.client_secret'),
                'scope'         => config('services.azure.scope'),
            ]
        );

        if ($res->failed()) {
            throw new \Exception('Get token API failed: ' . $res->body());
        }

        return $res['access_token'];
    }

    private function call(string $endpoint, array $payload = []): \Illuminate\Http\Client\Response
    {
        $token = $this->getAzureToken();

        $json    = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $escaped = rawurlencode($json);

        $params = [
            'request' => $escaped,
            'crc'     => strtoupper(md5($escaped)),
            'mid'     => config('services.safra.merchant_id'),
            'un'      => '',
        ];

        return Http::asForm()
            ->withHeaders([
                'Authorization'             => 'Bearer ' . $token,
                'Ocp-Apim-Subscription-Key' => config('services.safra.subscription_key'),
            ])
            ->post(
                rtrim(config('services.safra.base_url'), '/') . '/' . ltrim($endpoint, '/'),
                $params
            );
    }

    private function decryptAes256Cbc(string $encryptedHex): string
    {
        $password = '@6Viv8SUWS35wa';
        $salt     = '9f27d4a83b6c72ed4fb1a90c67d9e43f85a2cd7f';

        try {
            return $this->aesDecryptWithSalt($encryptedHex, $password, hex2bin($salt));
        } catch (\Throwable) {
            return $this->aesDecryptWithSalt($encryptedHex, $password, $salt);
        }
    }

    private function aesDecryptWithSalt(string $encryptedHex, string $password, string $saltBytes): string
    {
        $derived = hash_pbkdf2('sha1', $password, $saltBytes, 1000, 96, true);

        $key = substr($derived, 0, 32);
        $iv  = substr($derived, 32, 16);

        $decrypted = openssl_decrypt(
            hex2bin($encryptedHex),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            throw new \RuntimeException('Decrypt data failed: ' . openssl_error_string());
        }

        return $decrypted;
    }

    public function memberidByToken(array $itemData): array
    {
        $response = $this->call(
            'sfrControlTokenization/GetMemberIDByToken',
            $itemData
        );

        if ($response->failed()) {
            throw new \Exception('MemberID by token failed: ' . $response->body());
        }

        $data = $response->json();

        if (!empty($data['MemberID'])) {
            $data['MemberIDDecrypted'] = $this->decryptAes256Cbc($data['MemberID']);
        }

        return $data ?? [];
    }
}