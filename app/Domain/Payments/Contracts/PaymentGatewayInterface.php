<?php

namespace App\Domain\Payments\Contracts;

use App\Domain\Payments\Models\PaymentTransaction;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment transaction with provider and return redirect URL or gateway payload.
     *
     * @return array{checkout_url: string, provider_transaction_id: string}
     */
    public function initiatePayment(PaymentTransaction $transaction): array;

    /**
     * Verify incoming callback payload signature and integrity.
     */
    public function verifyCallback(array $payload, ?string $signature = null): bool;
}
