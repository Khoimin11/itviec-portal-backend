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
        $companies = DB::table('accounts_company_info')->get(['id', 'company_name']);
        $slugs = [];
        foreach ($companies as $company) {
            $slug = Str::slug($company->company_name);
            if ($slug === '' || strlen($slug) > 255 || isset($slugs[$slug]) || in_array($slug, ['profile', 'password', 'all-job', 'top-employers'], true)) {
                throw new RuntimeException('Cần đổi tên công ty có ID '.$company->id.' trước khi tạo slug duy nhất.');
            }
            $slugs[$slug] = $company->id;
        }

        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->string('slug')->nullable()->unique();
        });
        foreach ($slugs as $slug => $id) {
            DB::table('accounts_company_info')->where('id', $id)->update(['slug' => $slug]);
        }
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->string('slug')->nullable(false)->change();
            $table->unique('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('accounts_company_info', function (Blueprint $table): void {
            $table->dropUnique(['company_name']);
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
