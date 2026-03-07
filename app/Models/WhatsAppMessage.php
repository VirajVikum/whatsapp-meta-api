<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'wa_messages';

    protected $fillable = [
        'wa_message_id',
        'from_phone',
        'to_phone',
        'direction',
        'message_type',
        'body',
        'status',
        'status_updated_at',
        'payload',
    ];

    protected $casts = [
        'payload' => 'json',
        'status_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'from_phone', 'phone_number');
    }
}
