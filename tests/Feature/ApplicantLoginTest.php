<?php

namespace Tests\Feature;

use App\Models\AccountUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApplicantLoginTest extends TestCase
{
    use RefreshDatabase;

    private function login(array $overrides = []): TestResponse
    {
        return $this->postJson('/api/auth/login', array_replace([
            'email' => 'candidate@example.com',
            'password' => 'StrongPassword123!',
        ], $overrides));
    }

    private function account(string $token): TestResponse
    {
        $this->app['auth']->forgetGuards();

        return $this->withToken($token)->getJson('/api/auth/account');
    }

    public function test_login_issues_a_hashed_token_and_returns_the_frontend_user_contract(): void
    {
        $account = AccountUser::factory()->create(['email' => 'candidate@example.com']);
        $response = $this->login(['email' => ' CANDIDATE@Example.com '])
            ->assertOk()->assertJsonPath('isSuccess', true)
            ->assertJsonPath('data.user.id', $account->id)
            ->assertJsonPath('data.user.username', $account->name)
            ->assertJsonPath('data.user.role', 'APPLICANT')
            ->assertJsonMissingPath('data.user.password');

        $token = $response->json('data.accessToken');
        $stored = PersonalAccessToken::findToken($token);
        $this->assertNotNull($stored);
        $this->assertSame($account->id, $stored->tokenable_id);
        $this->assertNotSame($token, $stored->token);
        $this->assertTrue($stored->expires_at->isFuture());
        $this->assertEqualsWithDelta(now()->addDay()->timestamp, $stored->expires_at->timestamp, 2);
        $this->assertSame(['applicant'], $stored->abilities);
        $this->account($token)->assertOk()->assertJsonPath('data.email', $account->email);
    }

    public function test_wrong_password_and_unknown_email_return_the_same_error(): void
    {
        AccountUser::factory()->create(['email' => 'candidate@example.com']);
        $wrong = $this->login(['password' => 'WrongPassword123!'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $unknown = $this->login(['email' => 'unknown@example.com'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
        $this->assertSame($wrong->json('errors'), $unknown->json('errors'));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_requires_valid_input(): void
    {
        $this->postJson('/api/auth/login', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
        $this->login(['email' => 'invalid'])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_rejects_passwords_over_the_bcrypt_byte_limit(): void
    {
        AccountUser::factory()->create([
            'email' => 'candidate@example.com',
            'password' => 'Aa1!'.str_repeat('é', 34),
        ]);
        $this->login(['password' => 'Aa1!'.str_repeat('é', 35)])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_account_requires_authentication(): void
    {
        $this->getJson('/api/auth/account')->assertUnauthorized();
        $this->account('invalid-token')->assertUnauthorized();
    }

    public function test_expired_token_is_rejected(): void
    {
        $account = AccountUser::factory()->create();
        $token = $account->createToken('expired', ['applicant'], now()->subMinute());
        $this->account($token->plainTextToken)->assertUnauthorized();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->login()->assertUnprocessable();
        }
        $this->login()->assertStatus(429);
    }

    public function test_newly_registered_applicant_can_login(): void
    {
        $this->postJson('/api/auth/register', [
            'username' => 'New Candidate',
            'email' => 'candidate@example.com',
            'password' => 'StrongPassword123!',
            'termsAccepted' => true,
        ])->assertCreated();

        $response = $this->login()->assertOk();
        $this->account($response->json('data.accessToken'))
            ->assertOk()->assertJsonPath('data.username', 'New Candidate');
    }
}
