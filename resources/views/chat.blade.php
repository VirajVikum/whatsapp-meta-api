<x-layouts.chat>
    <div class="h-screen bg-gray-100 flex flex-col">
        <!-- Header -->
        <div class="bg-green-700 text-white p-4 shadow-lg flex items-center justify-between">
            <h1 class="text-2xl font-bold">WhatsApp</h1>
            <div class="flex gap-4">
                <button class="text-white hover:bg-green-600 p-2 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Main Chat Layout -->
        <div class="flex-1 flex min-h-0">
            <!-- Users/Conversations Sidebar -->
            <div class="w-96 bg-white border-r border-gray-200 flex flex-col overflow-hidden">
                <!-- Sidebar Header with Actions -->
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Chats</h2>
                    <button class="text-green-600 hover:bg-gray-100 p-2 rounded-full transition">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.5 1.5H4.75A2.25 2.25 0 002.5 3.75v11.5A2.25 2.25 0 004.75 17.5h10.5a2.25 2.25 0 002.25-2.25V10M6.5 6.5h7M6.5 11.5h7M17.5 1.5v6h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>

                <!-- Conversations/Users List -->
                <div class="flex-1 overflow-y-auto">
                    <livewire:conversation-list lazy />
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 bg-white flex flex-col overflow-hidden">
                <!-- Chat Header with User Info -->
                <div class="bg-white border-b border-gray-200 p-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-400 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                                <span id="avatar-initial">+</span>
                            </div>
                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900" id="chat-header-name">Select a contact</h3>
                            <p class="text-sm text-gray-500" id="chat-header-phone">Phone</p>
                        </div>
                    </div>

                    <!-- Right Actions -->
                    <div class="flex gap-4">
                        <button class="text-gray-600 hover:text-gray-900 transition hover:bg-gray-100 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </button>
                        <button class="text-gray-600 hover:text-gray-900 transition hover:bg-gray-100 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Messages and Form Container -->
                <div class="flex-1 flex flex-col overflow-hidden relative">
                    <livewire:message-chat lazy />
                    <div class="sticky bottom-0 left-0 w-full bg-white z-20">
                        <livewire:message-form />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', function() {
            // Listen for conversation selection to update header
            window.addEventListener('conversation-selected', function(e) {
                const phone = e.detail.phone || 'Contact';
                const name = e.detail.name || phone;
                document.getElementById('avatar-initial').textContent = name.charAt(0).toUpperCase();
                document.getElementById('chat-header-name').textContent = name;
                document.getElementById('chat-header-phone').textContent = phone;
            });
        });
    </script>
</x-layouts.chat>
