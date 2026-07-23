<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizen_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('phone', 30)->nullable();
            $table->string('preferred_locale', 5)->default('fr');
            $table->string('identity_level', 30)->default('unverified')->index();
            $table->timestamps();
        });

        Schema::create('territories', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('parent_id')->nullable()->constrained('territories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('type', 30)->index();
            $table->string('name_fr');
            $table->string('name_en');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('code', 50)->unique();
            $table->string('type', 30)->index();
            $table->string('name_fr');
            $table->string('name_en');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('administrative_units', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('administrative_units')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 50);
            $table->string('type', 30)->index();
            $table->string('name_fr');
            $table->string('name_en');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['organization_id', 'code']);
        });

        Schema::create('agent_assignments', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('organization_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('administrative_unit_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('territory_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['user_id', 'starts_at', 'ends_at']);
            $table->index(['organization_id', 'administrative_unit_id', 'territory_id'], 'agent_assignment_scope_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_assignments');
        Schema::dropIfExists('administrative_units');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('territories');
        Schema::dropIfExists('citizen_profiles');
    }
};
