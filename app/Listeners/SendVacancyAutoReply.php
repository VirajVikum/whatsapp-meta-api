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

        \Log::info('Processing WhatsApp message for auto-reply', [
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

        // Check if already sent auto-reply for this message (prevent duplicates)
        if (cache('autoreply_sent_'.$message->wa_message_id)) {
            \Log::debug('Auto-reply already sent for this message', ['id' => $message->wa_message_id]);

            return;
        }

        $body = $message->body ?? '';
        $bodyLower = strtolower($body);

        // Skip "ok/OK/Ok" messages - don't reply
        if (in_array($body, ['ok', 'OK', 'Ok'])) {
            \Log::debug('Skipping OK message - no reply needed', ['id' => $message->wa_message_id]);

            return;
        }

        // Get the phone number of the sender
        $senderPhone = $message->from_phone;

        if (! $senderPhone) {
            \Log::warning('Sender phone not found', ['id' => $message->wa_message_id]);

            return;
        }

            // Check if last incoming message from user was more than 24 hours ago
            $lastIncoming = \App\Models\WhatsAppMessage::where('from_phone', $senderPhone)
                ->where('direction', 'incoming')
                ->orderBy('created_at', 'desc')
                ->first();

            $needsTemplate = false;
            if ($lastIncoming) {
                $lastTime = $lastIncoming->created_at;
                if (!$lastTime || $lastTime->lt(now()->subHours(24))) {
                    $needsTemplate = true;
                }
            } else {
                // No previous incoming message, treat as new conversation
                $needsTemplate = true;
            }

            // Send template message if required
            if ($needsTemplate && !cache('template_sent_'.$senderPhone)) {
                try {
                    // Replace 'hello_world' with your actual template name
                    $templateName = 'demo_reply';
                    $templateLang = 'en';
                    \Duli\WhatsApp\Facades\WhatsApp::sendTemplate($senderPhone, $templateName, $templateLang);
                    cache()->put('template_sent_'.$senderPhone, true, now()->addHours(24));
                    \Log::info('Template message sent before auto-reply', [
                        'sender_phone' => $senderPhone,
                        'template' => $templateName,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send template message', [
                        'phone' => $senderPhone,
                        'error' => $e->getMessage(),
                    ]);
                    // Optionally: return or continue to auto-reply
                }
            }

        // Determine which auto-reply to send
        $autoReplyText = null;

        // Check for vacancy-related keywords
        $vacancyKeywords = ['vacancy', 'vacancies', 'opportunity', 'opportunities'];
        $hasVacancyKeyword = false;

        foreach ($vacancyKeywords as $keyword) {
            if (stripos($bodyLower, $keyword) !== false) {
                $hasVacancyKeyword = true;

                break;
            }
        }

        // Check for company/service-related keywords
        $companyKeywords = ['callcenter', 'auso', 'ausoworld'];
        $hasCompanyKeyword = false;

        foreach ($companyKeywords as $keyword) {
            if (stripos($bodyLower, $keyword) !== false) {
                $hasCompanyKeyword = true;

                break;
            }
        }

        // Determine which reply to send
        if ($hasVacancyKeyword) {
            $autoReplyText = 'Hello! Thank you for your interest. Could you please send us your CV so we can review your experience and get back to you? We look forward to hearing from you.';
        } elseif ($hasCompanyKeyword) {
            $autoReplyText = 'Thank you for your interest in Auso World Pvt Ltd. One of our Customer Service Executive will contact you as soon as possible.';
        } else {
            $autoReplyText = 'Hello,
Welcome to Auso World Pvt Ltd.
Please send us your inquiry. We will be happy to share more details with you.';
        }

        // Mark this message as processed (prevent duplicate auto-replies)
        cache()->put('autoreply_sent_'.$message->wa_message_id, true, now()->addHours(24));

        \Log::info('Sending auto-reply', [
            'sender_phone' => $senderPhone,
            'message_id' => $message->wa_message_id,
            'reply_type' => $hasVacancyKeyword ? 'vacancy' : ($hasCompanyKeyword ? 'company' : 'generic'),
        ]);

        try {
            // Send the auto-reply
            WhatsApp::sendMessage($senderPhone, $autoReplyText);

            \Log::info('Auto-reply sent successfully', [
                'sender_phone' => $senderPhone,
                'message_id' => $message->wa_message_id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send auto-reply', [
                'phone' => $senderPhone,
                'message_id' => $message->wa_message_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
