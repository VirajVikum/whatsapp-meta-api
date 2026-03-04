<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'welcome')->name('home');

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

require __DIR__.'/settings.php';


