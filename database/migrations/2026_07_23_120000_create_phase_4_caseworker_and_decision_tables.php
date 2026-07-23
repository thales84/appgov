<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('assigned_unit_id')->nullable()->after('citizen_id')->constrained('administrative_units')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('assigned_agent_id')->nullable()->after('assigned_unit_id')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::create('application_assignments', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('administrative_unit_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('status', 30)->default('active');
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->index(['organization_id', 'administrative_unit_id', 'status'], 'app_assign_scope_index');
            $table->index(['application_id', 'status']);
        });

        Schema::create('document_reviews', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('submitted_document_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 30)->default('valid'); // valid, invalid
            $table->text('notes')->nullable();
            $table->timestamp('reviewed_at');
            $table->timestamps();

            $table->index(['submitted_document_id', 'reviewed_at']);
        });

        Schema::create('application_messages', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->boolean('is_internal')->default(false)->index();
            $table->text('message');
            $table->timestamps();

            $table->index(['application_id', 'created_at']);
        });

        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('decision_maker_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('decision_type', 30); // approved, rejected, adjourned
            $table->text('reason_fr');
            $table->text('reason_en');
            $table->timestamp('decided_at');
            $table->timestamps();

            $table->index(['application_id', 'decided_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
        Schema::dropIfExists('application_messages');
        Schema::dropIfExists('document_reviews');
        Schema::dropIfExists('application_assignments');

        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['assigned_agent_id']);
            $table->dropForeign(['assigned_unit_id']);
            $table->dropColumn(['assigned_agent_id', 'assigned_unit_id']);
        });
    }
};
