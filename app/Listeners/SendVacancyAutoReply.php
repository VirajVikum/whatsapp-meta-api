<?php

namespace App\Listeners;

use Duli\WhatsApp\Events\WhatsAppMessageReceived;
use Duli\WhatsApp\Facades\WhatsApp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendVacancyAutoReply implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(WhatsAppMessageReceived $event): void
    {
        $message = $event->message;

        // Check if message body contains "vacancy" keyword (case-insensitive)
        if (! isset($message->body) || stripos($message->body, 'vacancy') === false) {
            return;
        }

        // Get the phone number of the sender
        $senderPhone = $message->from;

        if (! $senderPhone) {
            return;
        }

        // Prepare auto-reply message
        $autoReplyText = 'Hello! Thank you for your interest. Could you please send us your CV so we can review your experience and get back to you? We look forward to hearing from you.';

        try {
            // Send the auto-reply
            WhatsApp::sendMessage($senderPhone, $autoReplyText);
        } catch (\Exception $e) {
            \Log::error('Failed to send vacancy auto-reply', [
                'phone' => $senderPhone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
