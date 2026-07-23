<?php

namespace App\Domain\Payments\Services\Gateways;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Str;

class LocalMockPaymentGateway implements PaymentGatewayInterface
{
    public function initiatePayment(PaymentTransaction $transaction): array
    {
        $providerTxId = 'MOCK-TX-'.strtoupper(Str::random(10));

        $checkoutUrl = route('account.payments.callback.handle', [
            'provider' => 'local_mock',
            'transaction_id' => $providerTxId,
            'idempotency_key' => $transaction->idempotency_key,
            'status' => 'successful',
        ]);

        return [
            'checkout_url' => $checkoutUrl,
            'provider_transaction_id' => $providerTxId,
        ];
    }

    public function verifyCallback(array $payload, ?string $signature = null): bool
    {
        // Mock gateway accepts test payloads with idempotency_key
        return isset($payload['idempotency_key']) || isset($payload['provider_transaction_id']);
    }
}
