<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\WhatsAppMessage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MessageChat extends Component
{
    public ?int $conversationId = null;

    public int $messagesLimit = 5;

    public int $messagesOffset = 0;

    public bool $showLoadMore = false;

    public function mount(?int $conversationId = null): void
    {
        $this->conversationId = $conversationId;
        $this->updateLoadMoreState();
    }

    #[On('conversation-selected')]
    public function onConversationSelected(int $conversationId): void
    {
        $this->setConversation($conversationId);
    }

    #[On('message-sent')]
    public function onMessageSent(): void
    {
        $this->messagesOffset = 0;
        $this->updateLoadMoreState();
        $this->dispatch('refresh-messages');
    }

    #[Computed]
    public function conversation()
    {
        if (! $this->conversationId) {
            return null;
        }

        return Conversation::find($this->conversationId);
    }

    #[Computed]
    public function messages()
    {
        if (! $this->conversation) {
            return [];
        }

        $phone = $this->conversation->phone_number;

        return WhatsAppMessage::where(function ($query) use ($phone) {
            $query->where('from_phone', $phone)
                ->orWhere('to_phone', $phone);
        })
            ->orderBy('created_at', 'desc')
            ->skip($this->messagesOffset)
            ->take($this->messagesLimit)
            ->get()
            ->reverse()
            ->toArray();
    }

    public function loadOlderMessages(): void
    {
        $this->messagesOffset += $this->messagesLimit;
        $this->updateLoadMoreState();
    }

    private function updateLoadMoreState(): void
    {
        if (! $this->conversation) {
            $this->showLoadMore = false;

            return;
        }

        $totalMessages = WhatsAppMessage::where(function ($query) {
            $phone = $this->conversation->phone_number;
            $query->where('from_phone', $phone)
                ->orWhere('to_phone', $phone);
        })->count();

        $this->showLoadMore = ($this->messagesOffset + $this->messagesLimit) < $totalMessages;
    }

    public function setConversation(int $conversationId): void
    {
        $this->conversationId = $conversationId;
        $this->messagesOffset = 0;
        $this->updateLoadMoreState();
    }

    public function render()
    {
        return view('livewire.message-chat');
    }
}
