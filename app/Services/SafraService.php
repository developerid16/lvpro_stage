<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class SafraService
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
            throw new \Exception('Azure token failed');
        }

        return $res['access_token'];
    }

    /**
     * COMMON SAFRA CALL
     *
     * @param string $endpoint
     * @param array  $payload
     * @param string $mode   'request' | 'params'
     */
    public function call(string $endpoint, array $payload = [], string $mode = 'params')
    {
        $token = $this->getAzureToken();

        $params = [];
        $crcSource = '';

        // 🔹 MODE 1: request=JSON (GetMemberCheckIn)
        if ($mode === 'request') {
            $json    = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $escaped = rawurlencode($json);

            $params['request'] = $escaped;
            $crcSource = $escaped;
        }

        // 🔹 MODE 2: Name/Value params (most APIs)
        else {
            foreach ($payload as $item) {
                $escaped = rawurlencode(json_encode($item['Value']));
                $params[$item['Name']] = $escaped;
                $crcSource .= $escaped;
            }

            if ($crcSource === '') {
                $crcSource = strtoupper(config('services.safra.merchant_id'));
            }
        }

        $params['crc'] = strtoupper(md5($crcSource));
        $params['mid'] = config('services.safra.merchant_id');
        $params['un']  = '';

        return Http::asForm()
        ->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Ocp-Apim-Subscription-Key' => config('services.safra.subscription_key'),
            'User-Agent' => 'Mozilla/5.0',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept-Encoding' => 'gzip, deflate',
        ])
        ->post(
            rtrim(config('services.safra.base_url'), '/') . '/' . ltrim($endpoint, '/'),
            $params
        );
    }
}
