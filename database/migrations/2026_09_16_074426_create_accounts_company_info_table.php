<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_company_info', function (Blueprint $table): void {
            $table->id();
            $table->string('contact_name');
            $table->string('position');
            $table->string('email')->unique();
            $table->string('phone_number', 20);
            $table->string('source')->nullable();
            $table->string('company_name');
            $table->string('location');
            $table->string('website', 2048)->nullable();
            $table->string('status', 20)->default('active');
            $table->string('password')->nullable();
            $table->timestamp('terms_accepted_at');
            $table->string('password_setup_token', 64)->nullable();
            $table->timestamp('password_setup_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_company_info');
    }
};
