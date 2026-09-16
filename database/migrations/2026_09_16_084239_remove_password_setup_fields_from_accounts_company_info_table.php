<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->dropColumn(['password_setup_token', 'password_setup_expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->string('password_setup_token', 64)->nullable();
            $table->timestamp('password_setup_expires_at')->nullable();
        });
    }
};
