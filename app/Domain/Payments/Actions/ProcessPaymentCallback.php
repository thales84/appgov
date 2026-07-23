<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\InvoiceStatus;
use App\Domain\Payments\Enums\PaymentTransactionStatus;
use App\Domain\Payments\Models\Payment;
use App\Domain\Payments\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LogicException;

class ProcessPaymentCallback
{
    public function __construct(
        private readonly GeneratePaymentReceiptPdf $generatePaymentReceiptPdf
    ) {}

    public function execute(string $idempotencyKey, string $status, array $payload = []): Payment
    {
        return DB::transaction(function () use ($idempotencyKey, $status, $payload) {
            $transaction = PaymentTransaction::query()
                ->with('invoice.application.citizen')
                ->where('idempotency_key', $idempotencyKey)
                ->orWhere('provider_transaction_id', $idempotencyKey)
                ->lockForUpdate()
                ->firstOrFail();

            // IDEMPOTENCY CHECK: If already successful and payment exists, return it!
            if ($transaction->status === PaymentTransactionStatus::Successful && $transaction->payment) {
                return $transaction->payment;
            }

            if ($status !== 'successful') {
                $transaction->update([
                    'status' => PaymentTransactionStatus::Failed,
                    'raw_payload' => $payload,
                    'completed_at' => now(),
                ]);

                throw new LogicException('Payment callback reported transaction failure or cancellation.');
            }

            $transaction->update([
                'status' => PaymentTransactionStatus::Successful,
                'raw_payload' => $payload,
                'completed_at' => now(),
            ]);

            $invoice = $transaction->invoice;
            $invoice->update([
                'status' => InvoiceStatus::Paid,
                'paid_at' => now(),
            ]);

            $paymentRef = 'PAY-'.date('Y').'-'.strtoupper(Str::random(8));

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_transaction_id' => $transaction->id,
                'payment_reference' => $paymentRef,
                'amount_minor' => $transaction->amount_minor,
                'currency' => $transaction->currency,
                'reconciled_at' => now(),
            ]);

            activity('payments')
                ->causedBy($invoice->application->citizen)
                ->performedOn($payment)
                ->event('payment.reconciled')
                ->withProperties([
                    'payment_reference' => $paymentRef,
                    'amount_minor' => $transaction->amount_minor,
                    'currency' => $transaction->currency,
                    'idempotency_key' => $idempotencyKey,
                ])
                ->log('Payment reconciled and quittance created.');

            $this->generatePaymentReceiptPdf->execute($payment);

            return $payment;
        });
    }
}
