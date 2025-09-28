<?php

namespace Grav\Plugin\Przelewy24;

class TransactionRegister
{
    private $merchantId;
    private $crc;
    private $apiKey;

    public function __construct(string $merchantId, string $crc, string $apiKey)
    {
        $this->merchantId = $merchantId;
        $this->crc = $crc;
        $this->apiKey = $apiKey;
    }

    public function createToken(array $data): ?string
    {
        // Use configured timezone or default to Europe/Warsaw
        $timezone = $data['timezone'] ?? 'Europe/Warsaw';
        date_default_timezone_set($timezone);
        //Script which generate sessionID
        $pre_sessionID = date("Y/m/d/H/i/s");
        $p24_sessionID = md5($pre_sessionID);

        $signData = [
            'sessionId' => $p24_sessionID,
            'merchantId' => (int)$this->merchantId,
            'amount' => (int)$data['amount'],
            'currency' => $data['currency'],
            'crc' => $this->crc
        ];

        $jsonString = json_encode($signData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $sign = hash('sha384', $jsonString);

        $json = [
            'merchantId' => (int)$this->merchantId,
            'posId' => (int)$this->merchantId,
            'sessionId' => $p24_sessionID,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'description' => $data['description'],
//            'regulationAccept' => false,
            'email' => $data['email'],
            'urlStatus' => $data['urlStatus'] . '/przelewy24/status',
            'urlReturn' => $data['urlReturn'] . '/',
            'country' => $data['country'] ?? 'PL',
            'language' => $data['language'] ?? 'pl',
            'sign' => $sign,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Authorization: Basic " . base64_encode($this->merchantId . ":" . $this->apiKey)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $apiUrl = ($data['sandbox'] ?? false)
            ? 'https://sandbox.przelewy24.pl/api/v1/transaction/register'
            : 'https://secure.przelewy24.pl/api/v1/transaction/register';
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $enc_response = json_decode($response, true);
            return $enc_response["data"]["token"] ?? null;
        }

        return null;
    }
}
