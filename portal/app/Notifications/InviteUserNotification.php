<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class InviteUserNotification extends ResetPassword
{
    public function toMail(mixed $notifiable): MailMessage
    {
        $businessName = Setting::get('business_name', 'Traitor.dev');
        $url = url(route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false));

        return (new MailMessage)
            ->subject("You've been invited to {$businessName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You've been invited to join {$businessName}.")
            ->action('Set up your account', $url)
            ->line('This link will expire in 60 minutes.')
            ->line('If you did not expect this invitation, you can ignore this email.');
    }
}
