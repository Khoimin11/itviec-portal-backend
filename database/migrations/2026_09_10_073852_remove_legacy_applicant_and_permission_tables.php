<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            $missingAccounts = DB::table('users as legacy')
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')->from('accounts_user as account')
                        ->whereColumn('account.id', 'legacy.id')
                        ->whereColumn('account.email', 'legacy.email');
                })->exists();

            if ($missingAccounts) {
                throw new RuntimeException('Legacy users have not all been copied to accounts_user. Cleanup stopped.');
            }
        }

        if (DB::table('personal_access_tokens')->where('tokenable_type', 'App\\Models\\User')->exists()) {
            throw new RuntimeException('Legacy user tokens still exist. Migrate or revoke them before cleanup.');
        }

        foreach ([
            'role_has_permissions', 'model_has_permissions', 'model_has_roles',
            'permissions', 'roles', 'applicants', 'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        throw new RuntimeException('This cleanup cannot restore deleted data. Restore the database backup instead.');
    }
};
