<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('category', 50);
            $table->unsignedSmallInteger('retention_years')->default(10);
            $table->string('legal_basis')->nullable();
            $table->timestamps();

            $table->index(['category', 'organization_id']);
        });

        Schema::create('archive_packages', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('application_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->char('package_hash', 64); // SHA-256
            $table->string('storage_path');
            $table->json('manifest');
            $table->timestamp('sealed_at');
            $table->foreignId('archived_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['application_id', 'sealed_at']);
        });

        Schema::create('audit_exports', function (Blueprint $table) {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('export_type', 50);
            $table->json('filters')->nullable();
            $table->timestamp('exported_at');
            $table->timestamps();

            $table->index(['user_id', 'exported_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_exports');
        Schema::dropIfExists('archive_packages');
        Schema::dropIfExists('retention_policies');
    }
};
