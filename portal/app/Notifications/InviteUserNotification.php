<?php

namespace App\Notifications;

use App\Models\Setting;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InviteUserNotification extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $businessName = Setting::get('business_name') ?? 'Traitor.dev';
        $url = url(route('invite.accept', ['token' => $this->token], false));

        return (new MailMessage)
            ->subject("You've been invited to {$businessName}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("You've been invited to join the {$businessName} organisation.")
            ->action('Accept invitation', $url)
            ->line('This link will remain valid until your invite is cancelled.');
    }
}
