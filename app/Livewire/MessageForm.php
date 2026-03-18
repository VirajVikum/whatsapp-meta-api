<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\WhatsAppMessage;
use Duli\WhatsApp\Facades\WhatsApp;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MessageForm extends Component
{
    public string $phoneInput = '';
    public function sendTemplateMessage(): void
    {
        $phone = '';
        if ($this->phoneInput) {
            $input = preg_replace('/[^0-9+]/', '', $this->phoneInput);
            if (str_starts_with($input, '+94')) {
                $phone = substr($input, 1);
            } elseif (str_starts_with($input, '94')) {
                $phone = $input;
            } elseif (str_starts_with($input, '0')) {
                $phone = '94' . substr($input, 1);
            } else {
                $phone = $input;
            }
        }

        if ($phone) {
            // Create conversation if not exists
            $conversation = Conversation::firstOrCreate(
                ['phone_number' => $phone],
                ['display_name' => $phone]
            );
            try {
                $templateName = 'demo_reply';
                $templateLang = 'en';
                WhatsApp::sendTemplate($phone, $templateName, $templateLang);
                cache()->put('template_sent_' . $phone, true, now()->addHours(24));
                $this->dispatch('template-message-sent');
                $this->phoneInput = '';
            } catch (\Exception $e) {
                $this->addError('body', 'Failed to send template message: ' . $e->getMessage());
            }
            return;
        }

        // Fallback to conversationId logic
        if (! $this->conversationId) {
            $this->addError('body', 'No conversation selected');
            return;
        }
        $conversation = Conversation::find($this->conversationId);
        if (! $conversation) {
            $this->addError('body', 'Conversation not found');
            return;
        }
        try {
            $templateName = 'demo_reply';
            $templateLang = 'en';
            WhatsApp::sendTemplate($conversation->phone_number, $templateName, $templateLang);
            cache()->put('template_sent_' . $conversation->phone_number, true, now()->addHours(24));
            $this->dispatch('template-message-sent');
        } catch (\Exception $e) {
            $this->addError('body', 'Failed to send template message: ' . $e->getMessage());
        }
    }
    public ?int $conversationId = null;

    #[Validate('required|string|max:4096')]
    public string $body = '';

    public function mount(?int $conversationId = null): void
    {
        $this->conversationId = $conversationId;
    }

    #[On('conversation-selected')]
    public function onConversationSelected(int $conversationId): void
    {
        $this->setConversation($conversationId);
    }

    public function setConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
    }

    public function sendMessage(): void
    {
        $this->validate();

        $phone = '';
        if ($this->phoneInput) {
            // Normalize phone number
            $input = preg_replace('/[^0-9+]/', '', $this->phoneInput);
            if (str_starts_with($input, '+94')) {
                $phone = substr($input, 1); // Remove '+'
            } elseif (str_starts_with($input, '94')) {
                $phone = $input;
            } elseif (str_starts_with($input, '0')) {
                $phone = '94' . substr($input, 1);
            } else {
                // Assume already normalized or fallback
                $phone = $input;
            }
        }

        if ($phone) {
            // Ensure conversation exists for left panel
            $conversation = Conversation::firstOrCreate(
                ['phone_number' => $phone],
                ['display_name' => $phone]
            );
            try {
                $response = WhatsApp::sendMessage($phone, $this->body);
                $messageId = $response['messages'][0]['id'] ?? 'temp_' . time();
                WhatsAppMessage::updateOrCreate(
                    ['wa_message_id' => $messageId],
                    [
                        'from_phone' => config('whatsapp.phone_id'),
                        'to_phone' => $phone,
                        'direction' => 'outgoing',
                        'message_type' => 'text',
                        'body' => $this->body,
                        'status' => 'sent',
                        'payload' => $response,
                    ]
                );
                $conversation->update([
                    'last_message_id' => $messageId,
                    'last_message_date' => now(),
                ]);
                $this->body = '';
                $this->phoneInput = '';
                $this->dispatch('message-sent');
            } catch (\Exception $e) {
                $this->addError('body', 'Failed to send message: ' . $e->getMessage());
                // Do not clear phoneInput on error
            }
            return;
        }

        // ...existing code for conversationId logic...
        if (! $this->conversationId) {
            $this->addError('body', 'No conversation selected');
            return;
        }
        $conversation = Conversation::find($this->conversationId);
        if (! $conversation) {
            $this->addError('body', 'Conversation not found');
            return;
        }
        try {
            // Check if last incoming message from user was more than 24 hours ago
            $lastIncoming = WhatsAppMessage::where('from_phone', $conversation->phone_number)
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
                $needsTemplate = true;
            }
            if ($needsTemplate && !cache('template_sent_' . $conversation->phone_number)) {
                try {
                    $templateName = 'demo_reply';
                    $templateLang = 'en';
                    WhatsApp::sendTemplate($conversation->phone_number, $templateName, $templateLang);
                    cache()->put('template_sent_' . $conversation->phone_number, true, now()->addHours(24));
                    usleep(500000);
                } catch (\Exception $e) {}
            }
            $response = WhatsApp::sendMessage(
                $conversation->phone_number,
                $this->body
            );
            $messageId = $response['messages'][0]['id'] ?? 'temp_' . time();
            WhatsAppMessage::updateOrCreate(
                ['wa_message_id' => $messageId],
                [
                    'from_phone' => config('whatsapp.phone_id'),
                    'to_phone' => $conversation->phone_number,
                    'direction' => 'outgoing',
                    'message_type' => 'text',
                    'body' => $this->body,
                    'status' => 'sent',
                    'payload' => $response,
                ]
            );
            $conversation->update([
                'last_message_date' => now(),
            ]);
            $this->body = '';
            $this->dispatch('message-sent');
        } catch (\Exception $e) {
            $this->addError('body', 'Failed to send message: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.message-form');
    }
}
