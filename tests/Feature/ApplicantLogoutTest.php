<?php

namespace Tests\Feature;

use App\Models\AccountUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApplicantLogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_revokes_only_the_current_token(): void
    {
        $account = AccountUser::factory()->create();
        $current = $account->createToken('current', ['applicant'], now()->addDay());
        $otherSession = $account->createToken('other-session', ['applicant'], now()->addDay());
        $otherAccount = AccountUser::factory()->create()->createToken('other-account');

        $this->withToken($current->plainTextToken)->postJson('/api/auth/logout')
            ->assertOk()->assertExactJson([
                'isSuccess' => true,
                'message' => 'Đăng xuất thành công',
                'data' => null,
            ]);

        $this->assertNull(PersonalAccessToken::findToken($current->plainTextToken));
        $this->assertNotNull(PersonalAccessToken::findToken($otherSession->plainTextToken));
        $this->assertNotNull(PersonalAccessToken::findToken($otherAccount->plainTextToken));
        $this->assertDatabaseCount('accounts_user', 2);

        $this->app['auth']->forgetGuards();
        $this->withToken($current->plainTextToken)->getJson('/api/auth/account')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($current->plainTextToken)->postJson('/api/auth/logout')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($otherSession->plainTextToken)->getJson('/api/auth/account')->assertOk();
    }

    public function test_logout_requires_a_valid_token(): void
    {
        $account = AccountUser::factory()->create();
        $valid = $account->createToken('valid');
        $this->postJson('/api/auth/logout')->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken('invalid-token')->postJson('/api/auth/logout')->assertUnauthorized();
        $this->assertNotNull(PersonalAccessToken::findToken($valid->plainTextToken));
    }

    public function test_expired_token_cannot_logout_other_sessions(): void
    {
        $account = AccountUser::factory()->create();
        $expired = $account->createToken('expired', ['applicant'], now()->subMinute());
        $valid = $account->createToken('valid');

        $this->withToken($expired->plainTextToken)->postJson('/api/auth/logout')->assertUnauthorized();
        $this->assertNotNull(PersonalAccessToken::findToken($valid->plainTextToken));
    }
}
