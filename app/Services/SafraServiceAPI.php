<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SafraServiceAPI
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
            throw new \Exception('Azure token failed: ' . $res->body());
        }

        return $res['access_token'];
    }

    /**
     * COMMON SAFRA CALL
     */
    public function call(string $endpoint, array $payload = [], string $mode = 'request')
    {
        $token = $this->getAzureToken();

        // 🔹 request=JSON mode
        $json    = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $escaped = rawurlencode($json);

        $params = [
            'request' => $escaped,
            'crc' => strtoupper(md5($escaped)),
            'mid' => config('services.safra.merchant_id'),
            'un'  => '',
        ];

        return Http::asForm()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Ocp-Apim-Subscription-Key' => config('services.safra.subscription_key'),
            ])
            ->post(
                rtrim(config('services.safra.base_url'), '/') . '/' . ltrim($endpoint, '/'),
                $params
            );
    }

    /**
     * Summary of serviceCall
     */
    public function serviceCall(string $endpoint, array $payload = [], string $mode = 'request')
    {
        $token = $this->getAzureToken();

        // 🔹 request=JSON mode
        $json    = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $escaped = rawurlencode($json);

        $params = [
            'request' => $escaped,
            'crc' => strtoupper(md5($escaped)),
            'mid' => config('services.safra.merchant_id'),
            'un'  => '',
        ];

        return Http::asForm()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Ocp-Apim-Subscription-Key' => config('services.safra.subscription_key'),
            ])
            ->post(
                rtrim('https://service-uat.safra.sg/proxy-uat/api', '/') . '/' . ltrim($endpoint, '/'),
                $params
            );
    }

    /**
     * Get Basic Detail Info By Modified
     */
    public function basicDetailInfoModified(string $lastModified, int $limit = 100): array
    {
        $response = $this->call(
            'sfrControlMember/GetBasicDetailInfoByModified',
            [
                'LastModifiedTime' => $lastModified,
                'Limit' => $limit
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }
        $data = json_decode($response->body()   , true);
        return $data['member_list'] ?? [];
    }

    /**
     * Get IG Basic Detail Info
     */
    public function getIGbasicdetail(string $lastModified, int $limit = 5): array
    {
        $response = $this->call(
            'sfrControlMember/GetBasicDetailIg',
            [
                'LastModifiedTime' => $lastModified,
                'Limit' => $limit,
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }
        $data = json_decode($response->body()   , true);
        return $data['member_list'] ?? [];
    }

    /**
     * Get Latest Transaction   
     */
    public function getLatestTransaction(string $lastModified, int $limit = 5): array
    {
        $response = $this->call(
            'sfrControlMember/GetLatestTransaction',
            [
                'LastModifiedTime' => $lastModified,
                'Limit' => $limit,
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }
        $data = json_decode($response->body()   , true);
        return $data['transaction_list'] ?? [];
    }

    /**
     * Get Customer Zone
     */
    public function getCustomerZone(string $lastModified, int $limit = 5): array
    {
        $response = $this->call(
            'sfrControlMember/GetCustomerZone',
            [
                'LastModifiedTime' => $lastModified,
                'Limit' => $limit,
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }
        $data = json_decode($response->body()   , true);
        return $data['transaction_list'] ?? [];
    }

    /**
     * Get Customer Zone
     */
    public function getInfoByMethod(string $lastModified, string $memberid)
    {
        if (!$memberid) {
            throw new \Exception('Member ID is required');
        }
        $response = $this->serviceCall(
            'sfrControlMember/GetBasicDetailInfoByMethod',
            [
                'LastModifiedTime' => $lastModified,
                'MemberId' => $memberid,
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }

    /** get shopping cart no */
    public function getShoppingCartNo(string $lastModified, string $memberid)
    {
        if (!$memberid) {
            throw new \Exception('Member ID is required');
        }
        $response = $this->serviceCall(
            'sfrControlCart/GetGlobalCartNo',
            [
                'LastModifiedTime' => $lastModified,
                'MemberId' => $memberid,
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }

    /** clear shopping cart */
    public function clearShoppingCart(string $lastModified, string $memberid)
    {
        if (!$memberid) {
            throw new \Exception('Member ID is required');
        }
        $response = $this->serviceCall(
            'sfrControlCart/ClearCart',
            [
                'LastModifiedTime' => $lastModified,
                'MemberId' => $memberid,
            ]
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }
}
