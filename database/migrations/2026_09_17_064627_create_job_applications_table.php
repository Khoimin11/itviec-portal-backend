<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->restrictOnDelete();
            $table->foreignId('applicant_id')->constrained('accounts_user')->restrictOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number', 20);
            $table->json('locations');
            $table->text('cover_letter')->nullable();
            $table->string('cv_public_id');
            $table->string('cv_original_name');
            $table->string('cv_mime_type', 100);
            $table->unsignedInteger('cv_size');
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['job_posting_id', 'applicant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
