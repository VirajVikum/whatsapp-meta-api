
<div class="flex-1 flex flex-col">
    @if($this->conversation)
        <!-- Load More Button (moved above messages) -->
        @if($this->showLoadMore)
            <div class="p-4 border-b border-gray-200 bg-white flex justify-center z-10">
                <button
                    wire:click="loadOlderMessages"
                    class="bg-green-50 hover:bg-green-100 text-green-700 font-medium py-2 px-6 rounded-full transition text-sm border border-green-200"
                >
                    ↑ Older Messages
                </button>
            </div>
        @endif
        <!-- Messages Container with Scrollbar -->
        <div class="relative flex-1">
            <div class="absolute inset-0 h-full w-full overflow-y-auto p-6 pb-32 space-y-4 bg-white scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100" style="background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22><rect fill=%22%23f5f5f5%22 width=%22100%22 height=%22100%22/></svg>')">
                @forelse($this->messages as $message)
                    <div class="flex {{ $message['direction'] === 'incoming' ? 'justify-start' : 'justify-end' }} animate-fadeIn">
                        <div class="max-w-xs {{ $message['direction'] === 'incoming' ? 'bg-white text-gray-900 shadow-sm' : 'bg-green-100 text-gray-900' }} rounded-lg px-4 py-2">
                            <p class="text-sm break-words">{{ $message['body'] }}</p>
                            <div class="flex items-center justify-end gap-1 mt-1">
                                <p class="text-xs {{ $message['direction'] === 'incoming' ? 'text-gray-500' : 'text-gray-600' }}">
                                    {{ \Carbon\Carbon::parse($message['created_at'])->format('H:i') }}
                                </p>
                                @if($message['direction'] === 'outgoing')
                                    <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                                    </svg>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex-1 flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <p class="text-sm">No messages yet</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        <div class="flex-1 flex items-center justify-center bg-white text-gray-400">
            <p class="text-sm">Select a conversation to start messaging</p>
        </div>
    @endif
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in;
    }
    /* Custom scrollbar for messages */
    .scrollbar-thin {
        scrollbar-width: thin;
    }
    .scrollbar-thumb-gray-300::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 8px;
    }
    .scrollbar-track-gray-100::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 8px;
    }
    .scrollbar-thin::-webkit-scrollbar {
        width: 8px;
    }
</style>

