<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('accounts_company_info')->cascadeOnDelete();
            $table->string('title');
            $table->string('label', 20)->nullable();
            $table->string('currency_salary', 3);
            $table->decimal('min_salary', 14, 2);
            $table->decimal('max_salary', 14, 2);
            $table->string('level', 30);
            $table->string('working_model', 30);
            $table->string('location', 100);
            $table->string('address')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->text('requirement')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('job_posting_skill', function (Blueprint $table): void {
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['job_posting_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posting_skill');
        Schema::dropIfExists('job_postings');
    }
};
