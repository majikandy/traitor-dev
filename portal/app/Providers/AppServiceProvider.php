<?php

namespace App\Providers;

use App\Models\SentEmail;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mime\Part\AbstractPart;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(MessageSent::class, function (MessageSent $event) {
            $message = $event->message;

            $to = collect($message->getTo())->map(fn($addr) => $addr->getAddress())->implode(', ');
            $subject = $message->getSubject() ?? '';

            $bodyHtml = null;
            $bodyText = null;

            $body = $message->getBody();

            if ($body instanceof \Symfony\Component\Mime\Part\TextPart) {
                if ($body->getMediaSubtype() === 'html') {
                    $bodyHtml = $body->getBody();
                } else {
                    $bodyText = $body->getBody();
                }
            } elseif ($body instanceof \Symfony\Component\Mime\Part\Multipart\AlternativePart) {
                foreach ($body->getParts() as $part) {
                    if ($part instanceof \Symfony\Component\Mime\Part\TextPart) {
                        if ($part->getMediaSubtype() === 'html') {
                            $bodyHtml = $part->getBody();
                        } else {
                            $bodyText = $part->getBody();
                        }
                    }
                }
            }

            SentEmail::create([
                'to'        => $to,
                'subject'   => $subject,
                'body_html' => $bodyHtml,
                'body_text' => $bodyText,
            ]);
        });
    }
}
