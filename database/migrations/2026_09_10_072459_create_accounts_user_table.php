<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts_user', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // Preserve existing applicant accounts and their original password hashes.
        $columns = [
            'id', 'name', 'email', 'password', 'email_verified_at',
            'terms_accepted_at', 'remember_token', 'created_at', 'updated_at',
        ];

        DB::table('accounts_user')->insertUsing(
            $columns,
            DB::table('users')->select($columns)
                ->whereIn('id', DB::table('applicants')->select('user_id'))
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts_user');
    }
};
