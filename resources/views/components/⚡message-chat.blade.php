<div class="flex-1 flex flex-col bg-gray-50">
    @if($this->conversation)
        <!-- Header -->
        <div class="border-b border-gray-200 bg-white p-4">
            <h3 class="font-semibold text-gray-900">
                {{ $this->conversation->display_name ?? $this->conversation->phone_number }}
            </h3>
        </div>

        <!-- Messages Container -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            @forelse($this->messages as $message)
                <div class="flex {{ $message['direction'] === 'incoming' ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-xs {{ $message['direction'] === 'incoming' ? 'bg-white border border-gray-200' : 'bg-blue-500 text-white' }} rounded-lg p-3 shadow-sm">
                        <p class="text-sm break-words">{{ $message['body'] }}</p>
                        <p class="text-xs {{ $message['direction'] === 'incoming' ? 'text-gray-500' : 'text-blue-100' }} mt-1">
                            {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-center h-full text-gray-500">
                    <p>No messages yet</p>
                </div>
            @endforelse
        </div>

        <!-- Load More Button -->
        @if($this->showLoadMore)
            <div class="p-4 border-t border-gray-200 bg-white">
                <button
                    wire:click="loadOlderMessages"
                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded transition text-sm"
                >
                    Older Messages
                </button>
            </div>
        @endif
    @else
        <div class="flex items-center justify-center h-full text-gray-500">
            <p>Select a conversation to view messages</p>
        </div>
    @endif
</div>
