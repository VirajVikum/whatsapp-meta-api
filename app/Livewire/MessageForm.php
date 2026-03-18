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
                // No previous incoming message, treat as new conversation
                $needsTemplate = true;
            }

            // Send template message if required
            if ($needsTemplate && !cache('template_sent_' . $conversation->phone_number)) {
                try {
                    // Replace 'hello_world' with your actual template name
                    $templateName = 'hello_world';
                    $templateLang = 'en_US';
                    WhatsApp::sendTemplate($conversation->phone_number, $templateName, $templateLang);
                    cache()->put('template_sent_' . $conversation->phone_number, true, now()->addHours(24));
                } catch (\Exception $e) {
                    // Optionally: handle template send failure
                }
            }

            // Send message via WhatsApp API
            $response = WhatsApp::sendMessage(
                $conversation->phone_number,
                $this->body
            );

            $messageId = $response['messages'][0]['id'] ?? 'temp_' . time();

            // Save or update message to database (webhook may have already saved it)
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

            // Update conversation
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
