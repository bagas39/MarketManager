<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private string $secretKey;
    private string $baseUrl = 'https://api.xendit.co';

    public function __construct()
    {
        $this->secretKey = config('services.xendit.secret_key');
    }

    public function createInvoice(string $externalId, int $amount, string $description): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->baseUrl}/v2/invoices", [
                    'external_id'          => $externalId,
                    'amount'               => $amount,
                    'description'          => $description,
                    'payment_methods'      => ['QRIS'],
                    'currency'             => 'IDR',
                    'success_redirect_url' => config('app.url') . '/',
                    'failure_redirect_url' => config('app.url') . '/',
                ]);

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('Xendit createInvoice error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
