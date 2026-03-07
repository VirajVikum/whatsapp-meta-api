<div class="w-full h-full flex flex-col bg-white">
    <div class="border-b border-gray-200 p-4">
        <h2 class="text-xl font-bold text-gray-900">Conversations</h2>
    </div>

    <div class="flex-1 overflow-y-auto">
        @forelse($this->conversations as $conversation)
            <button
                wire:click="selectConversation({{ $conversation->id }})"
                class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition {{ $this->selectedConversationId === $conversation->id ? 'bg-blue-50 border-l-4 border-l-blue-500' : '' }}"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                            {{ $conversation->display_name ?? $conversation->phone_number }}
                        </p>
                        @if($conversation->last_message_date)
                            <p class="text-sm text-gray-500">
                                {{ $conversation->last_message_date->diffForHumans() }}
                            </p>
                        @endif
                    </div>
                    @if($conversation->unread_count > 0)
                        <div class="ml-2 bg-blue-500 text-white text-xs rounded-full px-2 py-1">
                            {{ $conversation->unread_count }}
                        </div>
                    @endif
                </div>
            </button>
        @empty
            <div class="p-4 text-center text-gray-500">
                <p>No conversations yet</p>
            </div>
        @endforelse
    </div>
</div>
