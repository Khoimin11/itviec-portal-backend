<?php

namespace Tests\Feature;

use App\Models\AccountUser;
use App\Notifications\ApplicantRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class ApplicantRegistrationNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'username' => 'Nguyễn Văn An',
            'email' => ' Candidate@Example.com ',
            'password' => 'StrongPassword123!',
            'termsAccepted' => true,
        ], $overrides);
    }

    public function test_queues_one_welcome_email_for_the_registered_account(): void
    {
        Queue::fake();

        $this->postJson('/api/auth/register', $this->payload())->assertCreated();

        $account = AccountUser::sole();
        Queue::assertPushed(SendQueuedNotifications::class, function ($job) use ($account): bool {
            return $job->notification instanceof ApplicantRegistered
                && $job->channels === ['mail']
                && $job->notifiables->sole()->is($account)
                && $job->notifiables->sole()->routeNotificationFor('mail') === 'candidate@example.com';
        });
        Queue::assertPushed(SendQueuedNotifications::class, 1);
        $this->assertNull($account->email_verified_at);
    }

    public function test_database_queue_delivers_the_welcome_email(): void
    {
        config(['queue.default' => 'database']);
        $transport = Mail::mailer('array')->getSymfonyTransport();

        $this->postJson('/api/auth/register', $this->payload())->assertCreated();

        $this->assertDatabaseCount('jobs', 1);
        $this->assertCount(0, $transport->messages());

        $job = Queue::connection('database')->pop();
        $this->assertNotNull($job);
        $job->fire();

        $this->assertDatabaseCount('jobs', 0);
        $message = $transport->messages()->sole()->getOriginalMessage();
        $this->assertSame('candidate@example.com', $message->getTo()[0]->getAddress());
        $this->assertSame('Chào mừng bạn đến với ITViec', $message->getSubject());
    }

    public function test_does_not_notify_when_registration_is_invalid(): void
    {
        Notification::fake();

        $this->postJson('/api/auth/register', $this->payload(['password' => 'short']))
            ->assertUnprocessable();

        Notification::assertNothingSent();
        $this->assertDatabaseCount('accounts_user', 0);
    }

    public function test_does_not_notify_when_email_already_exists(): void
    {
        Notification::fake();
        AccountUser::factory()->create(['email' => 'candidate@example.com']);

        $this->postJson('/api/auth/register', $this->payload())->assertUnprocessable();

        Notification::assertNothingSent();
        $this->assertDatabaseCount('accounts_user', 1);
    }

    public function test_registration_still_succeeds_and_reports_queue_failure(): void
    {
        Exceptions::fake();
        $exception = new RuntimeException('Queue unavailable');
        Queue::shouldReceive('connection')->andThrow($exception);

        $this->postJson('/api/auth/register', $this->payload())->assertCreated();

        $this->assertDatabaseHas('accounts_user', ['email' => 'candidate@example.com']);
        Exceptions::assertReported(fn (RuntimeException $reported): bool => $reported === $exception);
    }

    public function test_welcome_email_renders_without_password_and_escapes_the_name(): void
    {
        $account = AccountUser::factory()->make(['name' => '<script>alert(1)</script>']);
        $message = (new ApplicantRegistered)->toMail($account);
        $html = (string) $message->render();

        $this->assertSame('Chào mừng bạn đến với ITViec', $message->subject);
        $this->assertStringContainsString('Tài khoản ứng viên của bạn đã được tạo thành công.', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString($account->password, $html);
        $this->assertStringNotContainsString('StrongPassword123!', $html);
    }
}
