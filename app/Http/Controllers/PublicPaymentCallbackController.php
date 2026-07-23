<?php

namespace App\Http\Controllers;

use App\Domain\Payments\Actions\ProcessPaymentCallback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicPaymentCallbackController extends Controller
{
    public function handle(
        Request $request,
        string $provider,
        ProcessPaymentCallback $action,
    ): RedirectResponse {
        $idempotencyKey = $request->input('idempotency_key') ?? $request->input('transaction_id');
        $status = $request->input('status', 'successful');

        abort_unless($idempotencyKey, 400, 'Missing idempotency key or transaction ID.');

        $payment = $action->execute($idempotencyKey, $status, $request->all());

        return to_route('account.applications.invoice.show', $payment->invoice->application)
            ->with('status', 'payment.successful');
    }
}
