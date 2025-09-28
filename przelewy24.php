<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;

require_once __DIR__ . '/classes/Przelewy24/TransactionRegister.php';
require_once __DIR__ . '/classes/Przelewy24/TransactionVerify.php';

use Grav\Plugin\Przelewy24\TransactionRegister;
use Grav\Plugin\Przelewy24\TransactionVerify;

class Przelewy24Plugin extends Plugin
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
        ];
    }

    /**
     * Get credentials with environment variables as priority, admin config as fallback
     */
    private function getCredentials(): array
    {
        // Load .env file if it exists
        $envFile = $this->grav['locator']->findResource('user://') . '/../.env';
        if (file_exists($envFile)) {
            $envVars = parse_ini_file($envFile);
            if ($envVars) {
                foreach ($envVars as $key => $value) {
                    $_ENV[$key] = $value;
                }
            }
        }

        $config = $this->config->get('plugins.przelewy24');

        return [
            'merchant_id' => $_ENV['P24_MERCHANT_ID'] ?? $config['merchant_id'] ?? '',
            'crc_key' => $_ENV['P24_CRC_KEY'] ?? $config['crc_key'] ?? '',
            'api_key' => $_ENV['P24_API_KEY'] ?? $config['api_key'] ?? ''
        ];
    }

    public function onPluginsInitialized(): void
    {
        if ($this->isAdmin()) {
            return;
        }

        $uri = $this->grav['uri'];
        if ($uri->path() === '/przelewy24/wspieraj') {
            $this->handleRequest();
            exit;
        }

        if ($uri->path() === '/przelewy24/status') {
            $this->handleStatus();
            exit;
        }

    }

    private function handleRequest()
    {
        $config = $this->config->get('plugins.przelewy24');
        $credentials = $this->getCredentials();

        if (empty($credentials['merchant_id']) || empty($credentials['crc_key']) || empty($credentials['api_key'])) {
            http_response_code(500);
            echo 'Payment system configuration error. Please contact administrator.';
            return;
        }

        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->grav['request'];

        // dane z POST (formularz)
        $post = $request->getParsedBody();

        if (empty($post['amount']) || empty($post['email'])) {
            http_response_code(400);
            echo 'Missing required fields: amount and email are required.';
            return;
        }

        // dane z POST
        $amount = $post['amount'];
        $description = $post['description'] ?? $config['payment_description'];
        $email = $post['email'];
        $currency = $post['currency'] ?? $config['currency'];

        $amount = $amount * 100;

        $transaction = new TransactionRegister(
            $credentials['merchant_id'],
            $credentials['crc_key'],
            $credentials['api_key'],
        );

        $baseUrl = $this->grav['base_url_absolute'];

        $data = [
            'amount' => $amount,
            'description' => $description,
            'email' => $email,
            'currency' => $currency,
            'country' => $config['country'],
            'language' => $config['language'],
            'sandbox' => $config['sandbox'],
            'timezone' => $config['timezone'] ?? 'Europe/Warsaw',
            'urlStatus' => $baseUrl,
            'urlReturn' => $baseUrl,
        ];

        $token = $transaction->createToken($data);

        if ($token) {
            $sandboxUrl = $config['sandbox'] ? 'https://sandbox.przelewy24.pl' : 'https://secure.przelewy24.pl';
            header('Location: ' . $sandboxUrl . '/trnRequest/' . $token);
        } else {
            http_response_code(500);
            echo 'Payment initialization failed. Please check your configuration and try again.';
        }
    }

    private function handleStatus()
    {
        $config = $this->config->get('plugins.przelewy24');
        $credentials = $this->getCredentials();

        /** @var \Psr\Http\Message\ServerRequestInterface $request */
        $request = $this->grav['request'];
        $post = $request->getParsedBody();

        $transactionVerify = new TransactionVerify(
            $credentials['merchant_id'],
            $credentials['crc_key'],
            $credentials['api_key'],
        );

        $success = $transactionVerify->verify($post ?? [], $config['sandbox']);

        if ($success) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
    }

}
