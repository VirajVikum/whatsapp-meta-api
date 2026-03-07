<?php

test('webhook verify endpoint returns challenge on valid token', function () {
    $verifyToken = config('whatsapp.verify_token');
    $challenge = 'test_challenge_123';

    $response = $this->get(route('whatsapp.webhook.verify', [], false).'?hub.mode=subscribe&hub.verify_token='.urlencode($verifyToken).'&hub.challenge='.urlencode($challenge));

    $response->assertStatus(200);
    expect($response->getContent())->toBe($challenge);
});

test('webhook verify endpoint rejects invalid token', function () {
    $response = $this->get(route('whatsapp.webhook.verify', [], false), [
        'hub_mode' => 'subscribe',
        'hub_verify_token' => 'invalid_token',
        'hub_challenge' => 'test_challenge_123',
    ]);

    $response->assertStatus(403);
});

test('webhook receive endpoint accepts valid payload with correct signature', function () {
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
                                'phone_number_id' => config('whatsapp.phone_id'),
                            ],
                            'messages' => [
                                [
                                    'from' => '1234567890',
                                    'id' => 'wamid.test.12345',
                                    'timestamp' => (string) now()->timestamp,
                                    'type' => 'text',
                                    'text' => [
                                        'body' => 'Test message',
                                    ],
                                ],
                            ],
                        ],
                        'field' => 'messages',
                    ],
                ],
            ],
        ],
    ];

    $payloadJson = json_encode($payload);
    $appSecret = config('whatsapp.app_secret');
    $signature = 'sha256='.hash_hmac('sha256', $payloadJson, $appSecret);

    $response = $this->postJson(route('whatsapp.webhook.receive', [], false), $payload, [
        'X-Hub-Signature-256' => $signature,
    ]);

    $response->assertStatus(200);
    expect($response->json('status'))->toBe('ok');
});

test('webhook receive endpoint rejects invalid signature', function () {
    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [
            [
                'changes' => [
                    [
                        'value' => [
                            'messages' => [
                                [
                                    'from' => '1234567890',
                                    'id' => 'wamid.test.12345',
                                    'timestamp' => (string) now()->timestamp,
                                    'type' => 'text',
                                    'text' => ['body' => 'Test'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson(route('whatsapp.webhook.receive', [], false), $payload, [
        'X-Hub-Signature-256' => 'sha256=invalid_signature',
    ]);

    $response->assertStatus(403);
    expect($response->json('error'))->toBe('Invalid signature');
});
