<?php

namespace App\Notifications;

use App\Models\AccountCompanyInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyRegistered extends Notification implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public string $password)
    {
        $this->onConnection('database')->beforeCommit();
    }

    public function via(AccountCompanyInfo $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(AccountCompanyInfo $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Thông báo tạo tài khoản thành công')
            ->view([
                'html' => 'mail.company-registered',
                'text' => 'mail.company-registered-text',
            ], ['email' => $notifiable->email, 'password' => $this->password]);
    }
}
