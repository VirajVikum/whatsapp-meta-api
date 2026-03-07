<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConversationSeeder extends Seeder
{
    public function run(): void
    {
        // Get unique phone numbers from messages
        $phoneNumbers = DB::table('wa_messages')
            ->selectRaw('DISTINCT COALESCE(from_phone, to_phone) as phone_number')
            ->pluck('phone_number');

        foreach ($phoneNumbers as $phone) {
            // Get the last message for this conversation
            $lastMessage = WhatsAppMessage::where('from_phone', $phone)
                ->orWhere('to_phone', $phone)
                ->orderByDesc('created_at')
                ->first();

            if ($lastMessage) {
                Conversation::updateOrCreate(
                    ['phone_number' => $phone],
                    [
                        'last_message_id' => $lastMessage->id,
                        'last_message_date' => $lastMessage->created_at,
                        'unread_count' => 0,
                    ]
                );
            }
        }
    }
}
