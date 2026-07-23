<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('code', 50)->unique();
            $table->string('name_fr');
            $table->string('name_en');
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('color_key', 30);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('organization_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('service_category_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 80)->unique();
            $table->string('name_fr');
            $table->string('name_en');
            $table->text('description_fr');
            $table->text('description_en');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['organization_id', 'service_category_id', 'is_active'], 'services_scope_index');
        });

        Schema::create('procedure_versions', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('service_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('status', 30)->default('draft')->index();
            $table->string('title_fr');
            $table->string('title_en');
            $table->text('summary_fr');
            $table->text('summary_en');
            $table->longText('description_fr')->nullable();
            $table->longText('description_en')->nullable();
            $table->longText('eligibility_fr')->nullable();
            $table->longText('eligibility_en')->nullable();
            $table->text('legal_basis_fr')->nullable();
            $table->text('legal_basis_en')->nullable();
            $table->boolean('is_demo')->default(false)->index();
            $table->timestamp('effective_from')->nullable()->index();
            $table->timestamp('effective_until')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('review_submitted_at')->nullable();
            $table->foreignId('review_submitted_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->text('review_note')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp('retired_at')->nullable();
            $table->foreignId('retired_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamps();

            $table->unique(['service_id', 'version_number']);
            $table->index(['service_id', 'status', 'effective_from'], 'procedure_versions_public_index');
        });

        Schema::create('procedure_steps', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('procedure_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 80);
            $table->unsignedSmallInteger('position');
            $table->string('name_fr');
            $table->string('name_en');
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('step_type', 30);
            $table->boolean('is_required')->default(true);
            $table->json('transition_rules')->nullable();
            $table->timestamps();

            $table->unique(['procedure_version_id', 'code']);
            $table->unique(['procedure_version_id', 'position']);
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('procedure_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('procedure_step_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 80);
            $table->unsignedSmallInteger('position');
            $table->string('field_type', 30);
            $table->string('label_fr');
            $table->string('label_en');
            $table->text('help_fr')->nullable();
            $table->text('help_en')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['procedure_version_id', 'code']);
            $table->unique(['procedure_step_id', 'position']);
        });

        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('procedure_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('procedure_step_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 80);
            $table->unsignedSmallInteger('position');
            $table->string('name_fr');
            $table->string('name_en');
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('condition_rules')->nullable();
            $table->json('allowed_mime_types')->nullable();
            $table->unsignedInteger('max_file_size_kb')->nullable();
            $table->timestamps();

            $table->unique(['procedure_version_id', 'code']);
            $table->unique(['procedure_version_id', 'position']);
        });

        Schema::create('procedure_rules', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('procedure_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 80);
            $table->unsignedSmallInteger('position');
            $table->string('rule_type', 30);
            $table->string('name_fr');
            $table->string('name_en');
            $table->text('description_fr');
            $table->text('description_en');
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['procedure_version_id', 'code']);
            $table->unique(['procedure_version_id', 'position']);
        });

        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('procedure_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('procedure_step_id')->nullable()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 80);
            $table->unsignedSmallInteger('position');
            $table->string('label_fr');
            $table->string('label_en');
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->unsignedBigInteger('amount_minor');
            $table->char('currency', 3);
            $table->unsignedTinyInteger('minor_unit_exponent')->default(0);
            $table->boolean('is_mandatory')->default(true);
            $table->string('due_when_fr')->nullable();
            $table->string('due_when_en')->nullable();
            $table->text('legal_basis_fr')->nullable();
            $table->text('legal_basis_en')->nullable();
            $table->timestamps();

            $table->unique(['procedure_version_id', 'code']);
            $table->unique(['procedure_version_id', 'position']);
            $table->index(['currency', 'amount_minor']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('citizen_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('procedure_version_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status', 40)->default('draft')->index();
            $table->char('draft_key', 64)->nullable()->unique();
            $table->timestamp('started_at');
            $table->timestamps();

            $table->index(['citizen_id', 'status', 'updated_at'], 'applications_citizen_index');
            $table->index(['procedure_version_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('fee_schedules');
        Schema::dropIfExists('procedure_rules');
        Schema::dropIfExists('document_requirements');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('procedure_steps');
        Schema::dropIfExists('procedure_versions');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};
