<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('batch_number', 50)->unique();
            $table->string('status', 30)->default('pending')->index();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('production_batch_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('status', 30)->default('queued')->index();
            $table->text('quality_notes')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
        });

        Schema::create('issued_documents', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('document_number', 50)->unique();
            $table->string('document_type', 50);
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'document_number']);
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('issued_document_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('status', 30)->default('dispatched')->index();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'status']);
        });

        Schema::create('delivery_proofs', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('delivery_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('recipient_name');
            $table->string('identity_document_number');
            $table->foreignId('agent_user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('proof_type', 50)->default('signature_receipt');
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('issued_documents');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_batches');
    }
};
