<div class="border-t border-gray-200 bg-white p-4 shadow-lg mb-4 rounded-xl">
    <form wire:submit="sendMessage" class="flex items-center gap-3">
        <!-- Phone Number Input -->
        <input
            type="text"
            wire:model="phoneInput"
            placeholder="Enter phone number"
            class="border border-gray-300 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:border-green-500 transition w-48"
        />
        <!-- Attachment Button -->
        <button
            type="button"
            class="text-green-600 hover:text-green-700 transition hover:bg-gray-100 p-2 rounded-full"
        >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 10H17a1 1 0 001-1v-3a1 1 0 00-1-1h-3z"/>
            </svg>
        </button>

        <!-- Message Input -->
        <textarea
            wire:model="body"
            placeholder="Type a message..."
            class="flex-1 border border-gray-300 rounded-2xl px-4 py-3 text-sm focus:outline-none focus:border-green-500 transition resize-none max-h-24"
            rows="1"
            required
            @keydown.enter.prevent="if (!$event.shiftKey) { $el.form.dispatchEvent(new Event('submit')); }"
        ></textarea>

        <!-- Emoji Button -->
        <button
            type="button"
            class="text-green-600 hover:text-green-700 transition hover:bg-gray-100 p-2 rounded-full"
        >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zm-5 5.5C7.67 13.75 8.9 13 10 13c1.1 0 2.33.75 2.97 1.5H7.02z" clip-rule="evenodd"/>
            </svg>
        </button>

        <!-- Send Button -->
        <button
            type="submit"
            class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-full transition transform hover:scale-110 duration-200"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,20.0151496 C0.8376543,20.8006365 0.99,21.89 1.77946707,22.52 C2.40,22.99 3.50612381,23.1 4.13399899,22.8429026 L21.714504,14.0454487 C22.6563168,13.5741566 23.1272231,12.6315722 22.9702544,11.4450211 L4.13399899,-0.553529618 C3.34915502,-0.980271321 2.40734225,-0.85906575 1.77946707,0.334485928 C0.994623095,0.9 0.837654326,2.10604706 1.15159189,2.89153395 L3.03521743,9.33252691 C3.03521743,9.48962426 3.19218622,9.64672161 3.50612381,9.64672161 L16.6915026,10.4322086 C16.6915026,10.4322086 17.1624089,10.4322086 17.1624089,9.95491625 L17.1624089,11.4450211 C17.1624089,12.0115037 16.6915026,12.4744748 16.6915026,12.4744748 Z"/>
            </svg>
        </button>

        <!-- Send Template Button -->
        <button
            type="button"
            wire:click="sendTemplateMessage"
            class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-full transition transform hover:scale-110 duration-200 ml-2"
            title="Send Template Message"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2.003 5.884l8.998-3.6a1 1 0 011.32.948v13.536a1 1 0 01-1.32.948l-8.998-3.6A1 1 0 012 13.536V6.464a1 1 0 01.003-.58z"/>
            </svg>
        </button>
    </form>

    @error('body')
        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
    @enderror
</div>

<style>
    textarea {
        resize: none;
        overflow: hidden;
    }
    textarea::-webkit-scrollbar {
        width: 6px;
    }
    textarea::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }
</style>
