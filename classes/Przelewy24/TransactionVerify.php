<?php

namespace Grav\Plugin\Przelewy24;

class TransactionVerify
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

    public function verify(array $data, bool $sandbox = false): bool
    {
        $signData = [
            'sessionId' => $data['sessionId'],
            'orderId'   => (int)$data['orderId'],
            'amount'    => (int)$data['amount'],
            'currency'  => $data['currency'],
            'crc'       => $this->crc
        ];

        $sign = hash('sha384', json_encode($signData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $body = [
            'merchantId' => (int)$this->merchantId,
            'posId'      => (int)$this->merchantId,
            'sessionId'  => $data['sessionId'],
            'amount'     => (int)$data['amount'],
            'currency'   => $data['currency'],
            'orderId'    => (int)$data['orderId'],
            'sign'       => $sign,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Basic " . base64_encode($this->merchantId . ":" . $this->apiKey)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        $apiUrl = $sandbox
            ? 'https://sandbox.przelewy24.pl/api/v1/transaction/verify'
            : 'https://secure.przelewy24.pl/api/v1/transaction/verify';
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $resp = json_decode($response, true);
            return isset($resp['data']) && $resp['data']['status'] === 'success';
        }

        return false;
    }
}
