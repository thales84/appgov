<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('reference', 30)->nullable()->unique()->after('status');
            $table->json('form_responses')->nullable()->after('draft_key');
            $table->json('snapshot')->nullable()->after('form_responses');
            $table->timestamp('submitted_at')->nullable()->after('started_at');
        });

        Schema::create('application_participants', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('participant_type', 30)->default('applicant');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->json('identity_data')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'participant_type']);
        });

        Schema::create('submitted_documents', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('document_requirement_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('disk', 30)->default('private');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size_bytes');
            $table->char('file_hash', 64);
            $table->string('status', 30)->default('pending');
            $table->text('quarantine_reason')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['application_id', 'document_requirement_id']);
        });

        Schema::create('application_events', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('event_type', 50);
            $table->string('label_fr');
            $table->string('label_en');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_events');
        Schema::dropIfExists('submitted_documents');
        Schema::dropIfExists('application_participants');

        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['reference', 'form_responses', 'snapshot', 'submitted_at']);
        });
    }
};
