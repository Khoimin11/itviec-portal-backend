<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('industries', function (Blueprint $table): void {
            $table->id();
            $table->string('name_en')->unique();
            $table->string('name_vi');
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->string('tagline')->nullable();
            $table->string('company_type')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->string('company_size')->nullable();
            $table->string('country')->nullable();
            $table->string('working_day')->nullable();
            $table->string('overtime_policy')->nullable();
            $table->text('overview')->nullable();
            $table->text('perks')->nullable();
            $table->string('logo_path')->nullable();
        });
        Schema::create('company_skill', function (Blueprint $table): void {
            $table->foreignId('company_id')->constrained('accounts_company_info')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->primary(['company_id', 'skill_id']);
        });

        foreach ([
            ['Software Development', 'Phát triển phần mềm'],
            ['IT Services and Consulting', 'Dịch vụ và tư vấn CNTT'],
            ['Banking and Financial Services', 'Ngân hàng và dịch vụ tài chính'],
            ['E-commerce', 'Thương mại điện tử'],
            ['Education', 'Giáo dục'],
            ['Telecommunications', 'Viễn thông'],
            ['Healthcare', 'Y tế'],
            ['Manufacturing', 'Sản xuất'],
            ['Other', 'Khác'],
        ] as [$english, $vietnamese]) {
            DB::table('industries')->insert([
                'name_en' => $english, 'name_vi' => $vietnamese,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        foreach (['PHP', 'Laravel', 'JavaScript', 'TypeScript', 'ReactJS', 'VueJS', 'Angular', 'NodeJS', 'Java', 'Spring', 'Python', 'C#', '.NET', 'Go', 'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'AWS', 'Docker', 'Kubernetes', 'Git', 'HTML5', 'CSS3', 'Figma', 'English'] as $name) {
            DB::table('skills')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_skill');
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('industry_id');
            $table->dropColumn(['tagline', 'company_type', 'company_size', 'country', 'working_day', 'overtime_policy', 'overview', 'perks', 'logo_path']);
        });
        Schema::dropIfExists('skills');
        Schema::dropIfExists('industries');
    }
};
