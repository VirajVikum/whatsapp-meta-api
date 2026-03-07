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
            // Send message via WhatsApp API
            $response = WhatsApp::sendMessage(
                $conversation->phone_number,
                $this->body
            );

            $messageId = $response['messages'][0]['id'] ?? 'temp_'.time();

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
            $this->addError('body', 'Failed to send message: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.message-form');
    }
}
