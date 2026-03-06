<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'welcome')->name('home');

// WhatsApp Webhook Routes - Meta calls this endpoint
Route::prefix('webhook')->group(function () {
    Route::get('/', [\Duli\WhatsApp\Http\Controllers\WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
    Route::post('/', [\Duli\WhatsApp\Http\Controllers\WhatsAppWebhookController::class, 'receive'])
        ->middleware('throttle:60,1')
        ->name('whatsapp.webhook.receive');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::get('/test-whatsapp', function () {
    try {
        if (!config('whatsapp.phone_id') || !config('whatsapp.token')) {
            return response()->json([
                'status' => 'error',
                'message' => 'WhatsApp not configured. Please set WHATSAPP_PHONE_ID and WHATSAPP_TOKEN in .env'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'WhatsApp package installed successfully!',
            'config' => [
                'phone_id' => config('whatsapp.phone_id') ? 'Configured' : 'Not set',
                'token' => config('whatsapp.token') ? 'Configured' : 'Not set',
                'api_version' => config('whatsapp.api_version'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
})->name('whatsapp.test.status');

Route::get('/send-whatsapp-test', function () {
    try {
        $phone = '94704017188';

        if (!$phone) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please provide phone parameter. Example: /send-whatsapp-test?phone=1234567890'
            ], 400);
        }

        $response = \Duli\WhatsApp\Facades\WhatsApp::sendTemplate(
            $phone,
            'welcome_message',
            'en'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'WhatsApp template message sent successfully!',
            'response' => $response
        ]);
    } catch (\Duli\WhatsApp\Exceptions\WhatsAppException $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'error_code' => $e->getErrorCode(),
            'response' => $e->getResponse()
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
})->name('whatsapp.test.send');

// Webhook testing route
Route::get('/test-webhook', function () {
    return view('whatsapp.webhook-test');
})->name('whatsapp.webhook.test');

// Simulate webhook payload for testing
Route::post('/test-webhook-send', function (\Illuminate\Http\Request $request) {
    try {
        $verifyToken = config('whatsapp.verify_token');
        $appSecret = config('whatsapp.app_secret');

        if (!$verifyToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'WHATSAPP_VERIFY_TOKEN not set in .env'
            ], 400);
        }

        if (!$appSecret) {
            return response()->json([
                'status' => 'error',
                'message' => 'WHATSAPP_APP_SECRET not set in .env'
            ], 400);
        }

        // Create test webhook payload
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => '123456789',
                    'changes' => [
                        [
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => '1023456789',
                                    'phone_number_id' => config('whatsapp.phone_id')
                                ],
                                'messages' => [
                                    [
                                        'from' => '1234567890',
                                        'id' => 'wamid.test.12345',
                                        'timestamp' => (string)now()->timestamp,
                                        'type' => 'text',
                                        'text' => [
                                            'body' => 'Test message from webhook tester'
                                        ]
                                    ]
                                ]
                            ],
                            'field' => 'messages'
                        ]
                    ]
                ]
            ]
        ];

        // Create signature
        $payload_json = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $payload_json, $appSecret);

        // Create a fake request object with the payload and signature
        $fakeRequest = \Illuminate\Http\Request::create(
            route('whatsapp.webhook.receive'),
            'POST',
            [],
            [],
            [],
            [
                'X-Hub-Signature-256' => $signature,
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_HUB_SIGNATURE_256' => $signature,
            ],
            $payload_json
        );

        // Directly call the webhook controller
        $controller = app(\Duli\WhatsApp\Http\Controllers\WhatsAppWebhookController::class);
        $response = $controller->receive($fakeRequest);

        return response()->json([
            'status' => 'success',
            'message' => 'Test webhook processed successfully',
            'signature_generated' => substr($signature, 0, 20) . '***',
            'response' => $response->getData(true)
        ]);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Webhook test error: ' . $e->getMessage() . ' ' . $e->getTraceAsString());
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
})->name('whatsapp.webhook.send.test');

// Webhook debugging
Route::get('/debug/webhook', function () {
    return response()->json([
        'webhook_url' => 'https://connectxp.ausoworld.com/webhook',
        'app_secret_set' => !!config('whatsapp.app_secret'),
        'app_secret_length' => strlen(config('whatsapp.app_secret') ?? ''),
        'verify_token_set' => !!config('whatsapp.verify_token'),
        'phone_id' => config('whatsapp.phone_id'),
        'queue_connection' => config('queue.default'),
        'jobs_in_queue' => DB::table('jobs')->count(),
        'messages_in_db' => DB::table('wa_messages')->count(),
    ]);
})->name('debug.webhook');

require __DIR__.'/settings.php';


