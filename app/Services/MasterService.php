<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MasterService
{
    private $url;
    private $publicKeyPath;
    
    public function __construct(){
        $this->url = "https://service-uat.safra.sg/proxy-uat/api";
        $this->publicKeyPath = storage_path('app/sol.api.public.key');
    }
    
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
     * Read RSA public key from XML file
     */
    private function readRsaPublicKeyXml(): array
    {
        if (!file_exists($this->publicKeyPath)) {
            throw new \Exception('Public key file not found: ' . $this->publicKeyPath);
        }

        $fileContent = file_get_contents($this->publicKeyPath);
        
        preg_match('/<BitStrength>(\d+)<\/BitStrength>/i', $fileContent, $bitMatch);
        preg_match('/<Modulus>([^<]+)<\/Modulus>/i', $fileContent, $modMatch);
        preg_match('/<Exponent>([^<]+)<\/Exponent>/i', $fileContent, $expMatch);

        if (!$bitMatch || !$modMatch || !$expMatch) {
            throw new \Exception('Invalid public key format');
        }

        return [
            'bitStrength' => (int)$bitMatch[1],
            'modulus' => $modMatch[1],
            'exponent' => $expMatch[1]
        ];
    }

    /**
     * Convert string to UTF-32 LE encoding
     */
    private function stringToUtf32Le(string $str): string
    {
        $result = '';
        $length = mb_strlen($str, 'UTF-8');
        
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($str, $i, 1, 'UTF-8');
            $codePoint = mb_ord($char, 'UTF-8');
            // Pack as 32-bit unsigned integer, little-endian
            $result .= pack('V', $codePoint);
        }
        
        return $result;
    }

    /**
     * Encode ASN.1 length
     */
    private function encodeAsn1Length(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }
        
        $lengthBytes = '';
        $temp = $length;
        while ($temp > 0) {
            $lengthBytes = chr($temp & 0xFF) . $lengthBytes;
            $temp >>= 8;
        }
        
        return chr(0x80 | strlen($lengthBytes)) . $lengthBytes;
    }

    /**
     * Encode ASN.1 integer
     */
    private function encodeAsn1Integer(string $data): string
    {
        // Add leading zero if first byte is >= 0x80
        if (ord($data[0]) >= 0x80) {
            $data = "\x00" . $data;
        }
        
        return "\x02" . $this->encodeAsn1Length(strlen($data)) . $data;
    }

    /**
     * Create PEM public key from modulus and exponent
     */
    private function createPemPublicKey(string $modulus, string $exponent): string
    {
        // Decode base64 modulus and exponent
        $n = base64_decode($modulus);
        $e = base64_decode($exponent);
        
        // Encode as ASN.1 integers
        $nEncoded = $this->encodeAsn1Integer($n);
        $eEncoded = $this->encodeAsn1Integer($e);
        
        // Create RSA public key sequence
        $rsaPublicKey = "\x30" . $this->encodeAsn1Length(strlen($nEncoded . $eEncoded)) . $nEncoded . $eEncoded;
        
        // RSA encryption algorithm identifier
        $algorithmIdentifier = "\x30\x0D" . // SEQUENCE of 13 bytes
                              "\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01" . // OID for rsaEncryption
                              "\x05\x00"; // NULL
        
        // Wrap in BIT STRING
        $bitString = "\x03" . $this->encodeAsn1Length(strlen($rsaPublicKey) + 1) . "\x00" . $rsaPublicKey;
        
        // Create final SEQUENCE
        $publicKeyInfo = "\x30" . $this->encodeAsn1Length(strlen($algorithmIdentifier . $bitString)) . 
                        $algorithmIdentifier . $bitString;
        
        // Convert to PEM format
        $pem = "-----BEGIN PUBLIC KEY-----\n";
        $pem .= chunk_split(base64_encode($publicKeyInfo), 64, "\n");
        $pem .= "-----END PUBLIC KEY-----\n";
        
        return $pem;
    }

    /**
     * Encrypt CRC using RSA-OAEP
     */
    private function encryptCrc(string $crc): string
    {
        try {
            $keyInfo = $this->readRsaPublicKeyXml();
            
            // Create PEM public key
            $pemKey = $this->createPemPublicKey($keyInfo['modulus'], $keyInfo['exponent']);
            
            // Debug: Log the PEM key
            \Log::info('Generated PEM Key:', ['pem' => $pemKey]);
            
            // Verify the key is valid
            $publicKey = openssl_pkey_get_public($pemKey);
            if ($publicKey === false) {
                throw new \Exception('Failed to load public key: ' . openssl_error_string());
            }
            
            // Convert CRC string to UTF-32 LE
            $bytes = $this->stringToUtf32Le($crc);
            
            \Log::info('CRC Original:', ['crc' => $crc]);
            \Log::info('CRC UTF-32 LE bytes:', ['bytes' => bin2hex($bytes), 'length' => strlen($bytes)]);
            
            // Calculate max chunk size for OAEP padding
            $keySize = $keyInfo['bitStrength'] / 8;
            $maxLength = (int)($keySize - 42); // OAEP padding overhead (SHA1)
            
            \Log::info('Encryption params:', [
                'keySize' => $keySize,
                'maxLength' => $maxLength,
                'dataLength' => strlen($bytes)
            ]);
            
            $dataLength = strlen($bytes);
            $iterations = (int)ceil($dataLength / $maxLength);
            $encryptedString = '';
            
            // Encrypt in chunks
            for ($i = 0; $i < $iterations; $i++) {
                $start = $maxLength * $i;
                $end = min($start + $maxLength, $dataLength);
                $chunk = substr($bytes, $start, $end - $start);
                
                \Log::info("Chunk $i:", ['start' => $start, 'end' => $end, 'chunkLength' => strlen($chunk)]);
                
                $encrypted = '';
                $result = openssl_public_encrypt(
                    $chunk,
                    $encrypted,
                    $publicKey,
                    OPENSSL_PKCS1_OAEP_PADDING
                );
                
                if ($result === false) {
                    throw new \Exception('Encryption failed for chunk ' . $i . ': ' . openssl_error_string());
                }
                
                $encryptedString .= base64_encode($encrypted);
            }
            
            // Free the key resource
            openssl_free_key($publicKey);
            
            \Log::info('Final encrypted CRC:', ['encrypted' => $encryptedString]);
            
            return $encryptedString;
            
        } catch (\Exception $e) {
            \Log::error('CRC Encryption error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * COMMON SAFRA CALL
     */
    public function call(string $endpoint, array $payload = [])
    {
        $token = $this->getAzureToken();

        // Build params based on whether we have payload
        $params = [
            'mid' => strtoupper(config('services.safra.merchant_id')),
            'un'  => '',
        ];

        $escaped = '';
        if (!empty($payload)) {
            // Serialize and escape payload
            $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $escaped = rawurlencode($json);
            $params['request'] = $escaped;
        }

        // Calculate MD5 checksum
        $crcData = !empty($escaped) ? $escaped : strtoupper(config('services.safra.merchant_id'));
        $crc = strtoupper(md5($crcData));
        
        \Log::info('CRC Calculation:', [
            'crcData' => $crcData,
            'crc' => $crc
        ]);
        
        // Encrypt CRC with RSA
        $encryptedCrc = $this->encryptCrc($crc);
        $params['crc'] = rawurlencode($encryptedCrc);

        \Log::info('Final Request params:', $params);

        $response = Http::asForm()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Ocp-Apim-Subscription-Key' => config('services.safra.subscription_key'),
                'User-Agent' => 'Mozilla/5.0 (SAFRA Online API Client)',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->timeout(30)
            ->post(
                rtrim($this->url, '/') . '/' . ltrim($endpoint, '/'),
                $params
            );

        \Log::info('API Response:', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return $response;
    }

    /**
     * Get Gender Options
     */
    public function getGender(): array
    {
        $response = $this->call('sfrControlEnumValue/GetGenderOptions', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Gender API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }

    /**
     * Get Marital Status Options
     */
    public function getMaritalStatus(): array
    {
        $response = $this->call('sfrControlEnumValue/GetMaritalStatusOptions', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Marital Status API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }

    /**
     * Get Card Type Options
     */
    public function getCardType(): array
    {
        $response = $this->call('sfrControlEnumValue/GetCardType', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Card Type API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }

    /**
     * Get Dependent Type Options
     */
    public function getDependentType(): array
    {
        $response = $this->call('sfrControlEnumValue/GetDependentTypeOptions', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Dependent Type API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }

    /**
     * Get Interest Group Options
     */
    public function getInterestGroup(): array
    {
        $response = $this->call('sfrControlInterestGroup/GetInterestClub', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Interest Group API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }

    /** Get Zone Options */
    public function getZone(): array
    {
        $response = $this->call('sfrControlEnumValue/GetZone', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Zone API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }

    /** Get Membership Code Options */
    public function getMembershipCode(): array
    {
        $response = $this->call('sfrControlEnumValue/GetMembershipTypeDetail', []);
        
        if ($response->failed()) {
            throw new \Exception('Get Membership Code API failed: ' . $response->body());
        }

        $data = json_decode($response->body(), true);
        return $data ?? [];
    }
}