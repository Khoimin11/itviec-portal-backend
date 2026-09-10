<?php

namespace Tests\Feature;

use App\Models\AccountUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterApplicantTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'username' => 'Nguyễn Văn An',
            'email' => 'candidate@example.com',
            'password' => 'StrongPassword123!',
            'termsAccepted' => true,
        ], $overrides);
    }

    public function test_registers_an_applicant_with_a_hashed_password(): void
    {
        $this->postJson('/api/auth/register', $this->payload([
            'username' => '  Nguyễn Văn An  ',
            'email' => ' Candidate@Example.com ',
        ]))->assertCreated()->assertExactJson([
            'isSuccess' => true,
            'message' => 'Đăng ký thành công',
            'data' => null,
        ]);

        $user = AccountUser::sole();
        $this->assertSame('Nguyễn Văn An', $user->name);
        $this->assertSame('candidate@example.com', $user->email);
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));
        $this->assertNotSame('StrongPassword123!', $user->password);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNull($user->email_verified_at);
        $this->assertDatabaseCount('accounts_user', 1);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_rejects_duplicate_email_after_normalization(): void
    {
        AccountUser::factory()->create(['email' => 'candidate@example.com']);

        $this->postJson('/api/auth/register', $this->payload([
            'email' => ' CANDIDATE@example.com ',
        ]))->assertUnprocessable()->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('accounts_user', 1);
    }

    #[DataProvider('invalidFields')]
    public function test_validates_registration_fields(string $field, mixed $value): void
    {
        $this->postJson('/api/auth/register', $this->payload([$field => $value]))
            ->assertUnprocessable()->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('accounts_user', 0);

    }

    public static function invalidFields(): array
    {
        return [
            'blank name' => ['username', '   '],
            'long name' => ['username', str_repeat('a', 256)],
            'invalid email' => ['email', 'invalid'],
            'array email' => ['email', ['candidate@example.com']],
            'short password' => ['password', 'Short123!'],
            'missing uppercase' => ['password', 'lowercase123!'],
            'missing lowercase' => ['password', 'UPPERCASE123!'],
            'missing number' => ['password', 'PasswordOnly!'],
            'missing symbol' => ['password', 'PasswordOnly123'],
            'too many bytes' => ['password', 'Aa1!'.str_repeat('é', 35)],
            'no consent' => ['termsAccepted', false],
            'admin role' => ['role', 'ADMIN'],
            'roles array' => ['roles', ['ADMIN']],
            'verified email' => ['email_verified_at', '2026-09-10'],
        ];
    }

    public function test_requires_all_fields(): void
    {
        $this->postJson('/api/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'email', 'password', 'termsAccepted']);
    }

    private function createLegacyTables(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('applicants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained();
        });
    }

    public function test_migrations_preserve_accounts_and_remove_legacy_tables(): void
    {
        $original = AccountUser::factory()->create();
        $this->createLegacyTables();
        DB::table('users')->insert((array) DB::table('accounts_user')->first());
        DB::table('applicants')->insert(['user_id' => $original->id]);

        $migration = require database_path('migrations/2026_09_10_072459_create_accounts_user_table.php');
        $migration->down();
        $migration->up();
        $cleanup = require database_path('migrations/2026_09_10_073852_remove_legacy_applicant_and_permission_tables.php');
        $cleanup->up();

        $account = AccountUser::sole();
        $this->assertSame($original->id, $account->id);
        $this->assertSame($original->email, $account->email);
        $this->assertSame($original->password, $account->password);
        foreach (['users', 'applicants', 'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'] as $table) {
            $this->assertFalse(Schema::hasTable($table));
        }
        $this->assertSame(AccountUser::class, config('auth.providers.users.model'));
    }

    public function test_cleanup_stops_if_a_legacy_account_has_not_been_copied(): void
    {
        $account = AccountUser::factory()->create();
        $this->createLegacyTables();
        DB::table('users')->insert((array) DB::table('accounts_user')->first());
        $account->delete();
        $cleanup = require database_path('migrations/2026_09_10_073852_remove_legacy_applicant_and_permission_tables.php');

        try {
            $cleanup->up();
            $this->fail('Cleanup must stop before deleting legacy users.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('not all been copied', $exception->getMessage());
            $this->assertDatabaseCount('users', 1);
            $this->assertTrue(Schema::hasTable('applicants'));
        }
    }

    public function test_limits_registration_requests(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/register', [])->assertUnprocessable();
        }

        $this->postJson('/api/auth/register', [])->assertStatus(429);
    }

    public function test_accepts_preflight_from_the_frontend(): void
    {
        $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type',
        ])->options('/api/auth/register')
            ->assertSuccessful()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }
}
