<?php

namespace App\Livewire;

use App\Models\Conversation;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConversationList extends Component
{
    public ?int $selectedConversationId = null;

    #[Computed]
    public function conversations()
    {
        return Conversation::orderByDesc('last_message_date')->get();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->selectedConversationId = $conversationId;
        $conversation = Conversation::find($conversationId);
        $this->dispatch('conversation-selected',
            conversationId: $conversationId,
            phone: $conversation->phone_number,
            name: $conversation->display_name ?? $conversation->phone_number
        );
    }

    public function render()
    {
        return view('livewire.conversation-list');
    }
}
