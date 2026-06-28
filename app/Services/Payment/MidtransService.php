<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MidtransService
{
    private string $serverKey;

    private string $clientKey;

    private bool $isProduction;

    private string $snapApiUrl;

    public function __construct()
    {
        $this->serverKey = (string) config('services.midtrans.server_key');
        $this->clientKey = (string) config('services.midtrans.client_key');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->snapApiUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create Snap token for the given order.
     *
     * @return array{snap_token: string, snap_url: string}
     *
     * @throws RuntimeException
     */
    public function createSnapToken(Order $order): array
    {
        $order->load(['items', 'customer', 'address']);

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->customer->name,
                'email' => $order->customer->email,
                'phone' => $order->customer->phone,
            ],
            'item_details' => $order->items->map(function ($item) {
                return [
                    'id' => $item->variant_sku,
                    'price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'name' => $item->product_name.' - '.$item->variant_size.' '.$item->variant_color,
                ];
            })->toArray(),
        ];

        // Add shipping cost as separate item if exists
        if ($order->shipping_cost > 0) {
            $payload['item_details'][] = [
                'id' => 'SHIPPING',
                'price' => $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Shipping Cost',
            ];
        }

        Log::info('Creating Midtrans Snap token', [
            'order_number' => $order->order_number,
            'amount' => $order->total,
        ]);

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->timeout(10)
            ->connectTimeout(5)
            ->post($this->snapApiUrl, $payload);

        if ($response->failed()) {
            $errorMessage = $response->json('error_messages.0', 'Unknown error');
            Log::error('Midtrans Snap token creation failed', [
                'order_number' => $order->order_number,
                'status' => $response->status(),
                'error' => $errorMessage,
                'response' => $response->json(),
            ]);

            throw new RuntimeException("Failed to create Snap token: {$errorMessage}");
        }

        $data = $response->json();

        return [
            'snap_token' => $data['token'],
            'snap_url' => $data['redirect_url'],
        ];
    }

    /**
     * Verify Midtrans webhook signature.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
