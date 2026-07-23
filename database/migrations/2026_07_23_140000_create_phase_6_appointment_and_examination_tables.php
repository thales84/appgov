<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('administrative_unit_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name_fr');
            $table->string('name_en');
            $table->text('address')->nullable();
            $table->unsignedInteger('daily_capacity')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
        });

        Schema::create('appointment_slots', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('location_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('max_capacity')->default(10);
            $table->unsignedInteger('booked_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location_id', 'starts_at', 'is_active']);
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('appointment_slot_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('citizen_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 30)->default('scheduled');
            $table->timestamp('scheduled_at');
            $table->timestamps();

            $table->index(['application_id', 'status']);
            $table->index(['citizen_id', 'scheduled_at']);
        });

        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('code', 50)->unique();
            $table->string('name_fr');
            $table->string('name_en');
            $table->unsignedInteger('passing_score')->nullable();
            $table->timestamps();
        });

        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('location_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('exam_type_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->date('session_date');
            $table->unsignedInteger('capacity')->default(20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location_id', 'session_date']);
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('examiner_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->unsignedInteger('score')->nullable();
            $table->string('result', 30); // passed, failed, absent, cancelled
            $table->text('notes')->nullable();
            $table->string('previous_result', 30)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('exam_sessions');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('appointment_slots');
        Schema::dropIfExists('locations');
    }
};
