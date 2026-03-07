<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\WhatsAppMessage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ConversationList extends Component
{
    public ?int $selectedConversationId = null;

    #[Computed]
    public function conversations()
    {
        // Get all unique phone numbers from incoming messages (from_phone only)
        $uniquePhones = WhatsAppMessage::select('from_phone')
            ->distinct()
            ->whereNotNull('from_phone')
            ->pluck('from_phone')
            ->filter()
            ->toArray();

        // Get or create conversations for each phone
        $conversations = [];
        foreach ($uniquePhones as $phone) {
            if (! $phone) {
                continue;
            }

            $conversation = Conversation::firstOrCreate(
                ['phone_number' => $phone],
                [
                    'display_name' => $phone,
                ]
            );

            // Update last message info
            $lastMessage = WhatsAppMessage::where(function ($query) use ($phone) {
                $query->where('from_phone', $phone)
                    ->orWhere('to_phone', $phone);
            })
                ->orderByDesc('created_at')
                ->first();

            if ($lastMessage) {
                $conversation->update([
                    'last_message_date' => $lastMessage->created_at,
                ]);
            }

            $conversations[] = $conversation;
        }

        // Sort by last message date
        usort($conversations, function ($a, $b) {
            $dateA = $a->last_message_date ? $a->last_message_date->timestamp : 0;
            $dateB = $b->last_message_date ? $b->last_message_date->timestamp : 0;

            return $dateB <=> $dateA;
        });

        return $conversations;
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
