<?php

use App\Http\Controllers\CustomWebhookController;
use Illuminate\Support\Facades\Route;

// WhatsApp Webhook Routes - Meta calls this endpoint
Route::prefix('webhook')->group(function () {
    // GET: Webhook verification (called by Meta during setup)
    Route::get('/', [\Duli\WhatsApp\Http\Controllers\WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
    
    // POST: Receive incoming messages and status updates
    Route::post('/', [\Duli\WhatsApp\Http\Controllers\WhatsAppWebhookController::class, 'receive'])
        ->middleware('throttle:60,1')
        ->name('whatsapp.webhook.receive');
});

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'whatsapp_configured' => !!config('whatsapp.phone_id'),
    ]);
})->name('health.check');

// Route::get('/webhook/whatsapp', [CustomWebhookController::class, 'verify']);
// Route::post('/webhook/whatsapp', [CustomWebhookController::class, 'receive']);

require __DIR__.'/settings.php';


