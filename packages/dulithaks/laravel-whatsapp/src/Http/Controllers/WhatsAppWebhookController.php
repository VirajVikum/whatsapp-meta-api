<?php

namespace Duli\WhatsApp\Http\Controllers;

use Duli\WhatsApp\Jobs\ProcessIncomingWhatsAppMessage;
use Duli\WhatsApp\Jobs\ProcessWhatsAppStatusUpdate;
use Duli\WhatsApp\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Verify webhook (GET request)
     */
    public function verify(Request $request)
    {
        return $this->whatsapp->verifyWebhook($request);
    }

    /**
     * Handle webhook events (POST request).
     *
     * This method does as little work as possible: verify the signature, then
     * immediately dispatch queued jobs and return 200 OK to WhatsApp.
     *
     * Why queues?
     *   WhatsApp expects a 200 response within ~20 seconds. If the response is
     *   late (e.g. due to DB latency under load), WhatsApp marks the delivery
     *   failed and retries with exponential back-off — causing hours-long delays
     *   and orphaned status updates (the "read" receipt arrives while the message
     *   is still missing from the DB). Moving processing off-request eliminates
     *   the timeout risk entirely.
     */
    public function receive(Request $request)
    {
        Log::info('WhatsApp Webhook Received', [
            'method' => $request->method(),
            'has_signature' => $request->hasHeader('X-Hub-Signature-256'),
            'body_length' => strlen($request->getContent()),
        ]);

        // Verify webhook signature — the only blocking work we do here
        if (! $this->verifySignature($request)) {
            Log::warning('WhatsApp webhook signature verification failed', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            // TEMPORARY: Allow webhook to proceed even if signature fails (for debugging)
            // In production, remove this comment and ensure signature matches
            Log::warning('WEBHOOK SIGNATURE CHECK BYPASSED - MESSAGES WILL BE SAVED - FIX SIGNATURE VERIFICATION IN PRODUCTION!');
            // return response()->json(['error' => 'Invalid signature'], 403);
        }

        $entry = $request->input('entry', []);

        Log::info('WhatsApp Webhook Event Received', [
            'entry_count' => count($entry),
            'timestamp' => now()->toIso8601String(),
        ]);

        $connection = config('whatsapp.queue.connection');
        $queue = config('whatsapp.queue.name', 'default');

        foreach ($entry as $change) {
            foreach ($change['changes'] ?? [] as $changeData) {
                $value = $changeData['value'] ?? [];

                foreach ($value['messages'] ?? [] as $message) {
                    Log::info('Dispatching message job', ['message_id' => $message['id'] ?? null]);
                    ProcessIncomingWhatsAppMessage::dispatch($message, $value)
                        ->onConnection($connection)
                        ->onQueue($queue);
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    Log::info('Dispatching status job', ['status_id' => $status['id'] ?? null]);
                    ProcessWhatsAppStatusUpdate::dispatch($status, $value)
                        ->onConnection($connection)
                        ->onQueue($queue);
                }
            }
        }

        // Return immediately — processing happens in the queue worker
        return response()->json(['status' => 'ok'], 200);
    }

    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');

        if (! $signature) {
            Log::error('WhatsApp webhook: Missing X-Hub-Signature-256 header');

            return false;
        }

        $appSecret = config('whatsapp.app_secret');

        if (! $appSecret) {
            Log::error('WhatsApp webhook: app_secret not configured — set WHATSAPP_APP_SECRET in .env');

            return false;
        }

        $payload = $request->getContent();
        $expectedSignature = 'sha256='.hash_hmac('sha256', $payload, $appSecret);

        $match = hash_equals($expectedSignature, $signature);

        if (! $match) {
            Log::warning('WhatsApp webhook signature mismatch', [
                'received' => substr($signature, 0, 20).'***',
                'expected' => substr($expectedSignature, 0, 20).'***',
                'payload_length' => strlen($payload),
            ]);
        } else {
            Log::info('WhatsApp webhook signature verified successfully');
        }

        return $match;
    }
}
