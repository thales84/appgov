<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Contracts\PaymentGatewayInterface;
use App\Domain\Payments\Enums\PaymentProvider;
use App\Domain\Payments\Enums\PaymentTransactionStatus;
use App\Domain\Payments\Models\Invoice;
use App\Domain\Payments\Models\PaymentTransaction;
use App\Domain\Payments\Services\Gateways\LocalMockPaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InitiatePaymentTransaction
{
    public function execute(Invoice $invoice, PaymentProvider $provider): array
    {
        return DB::transaction(function () use ($invoice, $provider) {
            $idempotencyKey = hash('sha256', "invoice:{$invoice->id}:".Str::uuid());

            $transaction = PaymentTransaction::create([
                'invoice_id' => $invoice->id,
                'provider_name' => $provider,
                'idempotency_key' => $idempotencyKey,
                'status' => PaymentTransactionStatus::Initiated,
                'amount_minor' => $invoice->total_amount_minor,
                'currency' => $invoice->currency,
                'initiated_at' => now(),
            ]);

            $gateway = $this->resolveGateway($provider);
            $gatewayResponse = $gateway->initiatePayment($transaction);

            $transaction->update([
                'provider_transaction_id' => $gatewayResponse['provider_transaction_id'],
            ]);

            activity('payments')
                ->causedBy($invoice->application->citizen)
                ->performedOn($transaction)
                ->event('payment.initiated')
                ->withProperties([
                    'provider' => $provider->value,
                    'idempotency_key' => $idempotencyKey,
                ])
                ->log('Payment transaction initiated.');

            return [
                'transaction' => $transaction,
                'checkout_url' => $gatewayResponse['checkout_url'],
            ];
        });
    }

    private function resolveGateway(PaymentProvider $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            PaymentProvider::LocalMock => new LocalMockPaymentGateway,
            default => new LocalMockPaymentGateway,
        };
    }
}
