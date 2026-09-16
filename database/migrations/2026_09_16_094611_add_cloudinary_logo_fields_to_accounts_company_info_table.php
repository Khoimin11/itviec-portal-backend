<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->string('logo_url', 2048)->nullable();
            $table->string('logo_public_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->dropColumn(['logo_url', 'logo_public_id']);
        });
    }
};
