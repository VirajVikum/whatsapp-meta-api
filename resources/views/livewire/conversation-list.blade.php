<div class="divide-y divide-gray-100 h-full overflow-y-auto">
    @forelse($this->conversations as $conversation)
        <button
            wire:click="selectConversation({{ $conversation->id }})"
            class="w-full p-3 hover:bg-gray-50 transition-colors duration-200 text-left focus:outline-none focus:bg-gray-100 active:bg-gray-100 border-l-4 {{ $this->selectedConversationId === $conversation->id ? 'bg-gray-100 border-l-green-600' : 'border-l-transparent' }}"
        >
            <div class="flex items-center gap-3">
                <!-- Avatar -->
                <div class="relative flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold text-base">
                        {{ strtoupper(substr($conversation->display_name ?? $conversation->phone_number, 0, 1)) }}
                    </div>
                    <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                </div>

                <!-- Contact Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-900 truncate">
                            {{ $conversation->phone_number }}
                        </h3>
                        <span class="text-xs text-gray-500 flex-shrink-0 ml-2">
                            {{ $conversation->last_message_date?->format('H:i') ?? '' }}
                        </span>
                    </div>
                </div>

                <!-- Unread Badge -->
                @if($conversation->unread_count > 0)
                    <div class="flex-shrink-0 bg-green-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                        {{ $conversation->unread_count }}
                    </div>
                @endif
            </div>
        </button>
    @empty
        <div class="p-8 text-center text-gray-500">
            <p>No conversations yet</p>
        </div>
    @endforelse
</div>
