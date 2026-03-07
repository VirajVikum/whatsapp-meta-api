<?php

namespace App\Listeners;

use Duli\WhatsApp\Events\WhatsAppMessageReceived;
use Duli\WhatsApp\Facades\WhatsApp;

class SendVacancyAutoReply
{
    /**
     * Handle the event.
     */
    public function handle(WhatsAppMessageReceived $event): void
    {
        $message = $event->message;

        \Log::info('Processing WhatsApp message for vacancy auto-reply', [
            'message_id' => $message->wa_message_id,
            'from_phone' => $message->from_phone,
            'direction' => $message->direction,
            'body' => $message->body,
        ]);

        // Only process incoming messages
        if ($message->direction !== 'incoming') {
            \Log::debug('Skipping non-incoming message', ['id' => $message->wa_message_id]);

            return;
        }

        // Check if message body contains "vacancy" keyword (case-insensitive)
        if (! $message->body || stripos($message->body, 'vacancy') === false) {
            \Log::debug('Message does not contain vacancy keyword', ['id' => $message->wa_message_id]);

            return;
        }

        // Get the phone number of the sender
        $senderPhone = $message->from_phone;

        if (! $senderPhone) {
            \Log::warning('Sender phone not found', ['id' => $message->wa_message_id]);

            return;
        }

        \Log::info('Sending vacancy auto-reply', [
            'sender_phone' => $senderPhone,
            'message_id' => $message->wa_message_id,
        ]);

        // Prepare auto-reply message
        $autoReplyText = 'Hello! Thank you for your interest. Could you please send us your CV so we can review your experience and get back to you? We look forward to hearing from you.';

        try {
            // Send the auto-reply
            WhatsApp::sendMessage($senderPhone, $autoReplyText);

            \Log::info('Vacancy auto-reply sent successfully', [
                'sender_phone' => $senderPhone,
                'message_id' => $message->wa_message_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send vacancy auto-reply', [
                'phone' => $senderPhone,
                'message_id' => $message->wa_message_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
