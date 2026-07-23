<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GeneratePaymentReceiptPdf
{
    public function execute(Payment $payment): string
    {
        $payment->loadMissing([
            'invoice.application',
            'invoice.lines',
        ]);

        $pdf = Pdf::loadView('pdf.payment_receipt', [
            'payment' => $payment,
        ]);

        $storagePath = "documents/{$payment->invoice->application->public_id}/payment_receipt_{$payment->payment_reference}.pdf";

        Storage::disk('private')->put(
            $storagePath,
            $pdf->output()
        );

        return $storagePath;
    }
}
