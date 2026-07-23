<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('public_id', 26)->nullable()->unique()->after('id');
            $table->string('account_type', 20)->default('citizen')->index()->after('email_verified_at');
            $table->string('status', 20)->default('active')->index()->after('account_type');
        });

        DB::table('users')
            ->whereNull('public_id')
            ->orderBy('id')
            ->eachById(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['public_id' => (string) Str::ulid()]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->char('public_id', 26)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['public_id']);
            $table->dropIndex(['account_type']);
            $table->dropIndex(['status']);
            $table->dropColumn(['public_id', 'account_type', 'status']);
        });
    }
};
