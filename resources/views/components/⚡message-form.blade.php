<div @if($this->conversationId) class="border-t border-gray-200 bg-white p-4" @else class="hidden" @endif>
    <form wire:submit="sendMessage" class="space-y-3">
        <div class="flex gap-2">
            <textarea
                wire:model="body"
                placeholder="Type a message..."
                class="flex-1 border border-gray-300 rounded-lg p-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                rows="2"
                required
            ></textarea>
            <button
                type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-6 rounded-lg transition"
            >
                Send
            </button>
        </div>

        @error('body')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror
    </form>
</div>
