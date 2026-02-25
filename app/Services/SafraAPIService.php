<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SafraAPIService
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
    public function getInfoByMethod(array $itemData)
    {
        $response = $this->serviceCall(
            'sfrControlMember/GetBasicDetailInfoByMethod',
            $itemData
        );
        if ($response->failed()) {
            throw new \Exception('get basic info by method API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }

    /** get shopping cart no */
    public function getShoppingCartNo(array $itemData)
    {
        $response = $this->serviceCall(
            'sfrControlCart/GetGlobalCartNo',
            $itemData
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }

    /** clear shopping cart */
    public function clearShoppingCart(array $itemData)
    {

        $response = $this->serviceCall(
            'sfrControlCart/ClearCart',
            $itemData
        );
        if ($response->failed()) {
            throw new \Exception('clear shopping cart API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }

    //** add merchandise item to cart */
    public function addMerchandiseItemCart(array $itemData)
    {
        $response = $this->serviceCall(
            'sfrControlCart/AddMerchandiseItemCart',
            $itemData
        );
        if ($response->failed()) {
            throw new \Exception('add merchandise item to cart API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }


    /** add payment method */
    public function addPaymentMethod(array $itemData)
    {
        $response = $this->serviceCall(
            'sfrControlCart/AddPayment',
            $itemData
        );
        if ($response->failed()) {
            throw new \Exception('add payment method API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }


    /** create payment receipt */
    public function createPaymentReceipt(array $itemData)
    {
        $response = $this->serviceCall(
            'sfrControlCart/CreateAXPayment',
            $itemData
        );
        if ($response->failed()) {
            throw new \Exception('create payment receipt API failed: ' . $response->body());
        }

        $data = json_decode($response->body()   , true);
        return $data ?? [];
    }

    /** get master list parameter */
    public function getSRPMasterListParameter(): array
    {
        $response = $this->call(
            'sfrControlMerchandiseRepository/GetSRPMerchandiseItemList',
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }
        $data = json_decode($response->body()   , true);
        return $data['parameter_list'] ?? [];
    }

    /** get merchandise item list */
    public function getMerchandiseItemList(): array
    {
        $response = $this->call(
            'sfrControlMerchandiseRepository/GetMerchandiseList'
        );
        if ($response->failed()) {
            throw new \Exception('SAFRA API failed: ' . $response->body());
        }
        $data = json_decode($response->body()   , true);
        return $data['merchandise_item_list'] ?? [];
    }

    
}
