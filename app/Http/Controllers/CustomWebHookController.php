<?php

namespace App\Http\Controllers;

use Duli\WhatsApp\Http\Controllers\WhatsAppWebhookController as BaseController;
use Illuminate\Support\Facades\Log;

class CustomWebhookController extends BaseController
{
    protected function handleMessage(array $message, array $value): void
    {
        parent::handleMessage($message, $value);

        // Your custom logic here
        $from = $message['from'];
        $type = $message['type'];

        if ($type === 'text') {
            $text = $message['text']['body'];
            // Process text message
        }
    }
}