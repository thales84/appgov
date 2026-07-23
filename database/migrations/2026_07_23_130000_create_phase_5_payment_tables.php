<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->string('status', 30)->default('unpaid')->index();
            $table->unsignedBigInteger('total_amount_minor');
            $table->char('currency', 3)->default('XAF');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fee_schedule_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('label_fr');
            $table->string('label_en');
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3)->default('XAF');
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('provider_name', 50);
            $table->string('provider_transaction_id')->nullable()->index();
            $table->char('idempotency_key', 64)->unique();
            $table->string('status', 30)->default('initiated')->index();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3)->default('XAF');
            $table->json('raw_payload')->nullable();
            $table->timestamp('initiated_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('invoice_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('payment_transaction_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('payment_reference', 50)->unique();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3)->default('XAF');
            $table->timestamp('reconciled_at');
            $table->timestamps();

            $table->index(['invoice_id', 'reconciled_at']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('payment_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('authorized_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3)->default('XAF');
            $table->text('reason');
            $table->timestamp('refunded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
