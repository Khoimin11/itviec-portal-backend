<?php

namespace App\Notifications;

use App\Models\AccountUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicantRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function via(AccountUser $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(AccountUser $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Chào mừng bạn đến với ITViec')
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Tài khoản ứng viên của bạn đã được tạo thành công.')
            ->line('Bạn có thể đăng nhập bằng email đã đăng ký để bắt đầu tìm kiếm việc làm.')
            ->salutation('Trân trọng, đội ngũ ITViec');
    }
}
