<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'display_name',
        'last_message_id',
        'last_message_date',
        'unread_count',
    ];

    protected $casts = [
        'last_message_date' => 'datetime',
        'unread_count' => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'from_phone', 'phone_number')
            ->orWhere('to_phone', $this->phone_number)
            ->orderBy('created_at', 'desc');
    }

    public function recentMessages(int $limit = 5): array
    {
        return WhatsAppMessage::where(function ($query) {
            $query->where('from_phone', $this->phone_number)
                ->orWhere('to_phone', $this->phone_number);
        })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->toArray();
    }
}
