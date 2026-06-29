<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BiteshipService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key');
        $this->baseUrl = config('services.biteship.base_url', 'https://api.biteship.com');
    }

    /**
     * Get shipping rates from Biteship for a cart going to a destination.
     *
     * @param  array  $items   Array of ['name', 'value', 'weight', 'quantity']
     * @param  string $destinationPostalCode  Destination postal code
     * @param  string|null $originPostalCode  Origin postal code (default from store address)
     * @param  string|null $couriers  Comma-separated courier codes
     * @return array  ['success' => bool, 'pricing' => array, 'error' => string|null]
     */
    public function getRates(array $items, string $destinationPostalCode, ?string $originPostalCode = null, ?string $couriers = null): array
    {
        if ($originPostalCode === null) {
            $storeAddress = \App\Models\StoreAddress::default();
            if (! $storeAddress) {
                return [
                    'success' => false,
                    'pricing' => [],
                    'error' => 'Alamat toko belum dikonfigurasi.',
                ];
            }
            $originPostalCode = $storeAddress->postal_code;
        }

        if ($couriers === null) {
            $couriers = 'jne,tiki,sicepat,pos,jnt,anteraja';
        }

        $payload = [
            'origin_postal_code' => (int) $originPostalCode,
            'destination_postal_code' => (int) $destinationPostalCode,
            'couriers' => $couriers,
            'items' => $items,
        ];

        try {
            // Biteship uses raw API key as Authorization header (no "Bearer" prefix)
            $response = Http::withHeaders([
                    'Authorization' => $this->apiKey,
                ])
                ->timeout(15)
                ->retry(2, 500, throw: false)
                ->post("{$this->baseUrl}/v1/rates/couriers", $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'pricing' => $data['pricing'] ?? [],
                    'error' => null,
                ];
            }

            Log::warning('Biteship rates error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $errorMsg = $response->json('error') ?? 'Gagal mengambil ongkir.';

            // Friendly message for insufficient balance
            if (str_contains($errorMsg, 'No sufficient balance')) {
                $errorMsg = 'Saldo Biteship tidak mencukupi. Hubungi admin untuk top up saldo.';
            }

            return [
                'success' => false,
                'pricing' => [],
                'error' => $errorMsg,
            ];
        } catch (ConnectionException $e) {
            Log::error('Biteship connection error: '.$e->getMessage());
            return [
                'success' => false,
                'pricing' => [],
                'error' => 'Tidak dapat terhubung ke server ongkir.',
            ];
        }
    }

    /**
     * Build items array from cart items with their weights.
     *
     * @param  \Illuminate\Support\Collection  $cartItems
     * @return array
     */
    public function buildItemsFromCart($cartItems): array
    {
        $items = [];

        foreach ($cartItems as $it) {
            $weight = 0;
            $name = $it->product_name;
            $variantName = $it->variant_name;

            if ($it->itemable) {
                $itemable = $it->itemable;
                $weight = (int) ($itemable->weight ?? 0);
            }

            $items[] = [
                'name' => $name . ($variantName ? ' - ' . $variantName : ''),
                'value' => (float) $it->price_snapshot,
                'weight' => max($weight, 100), // minimum 100 gram
                'quantity' => (int) $it->quantity,
            ];
        }

        return $items;
    }
}
