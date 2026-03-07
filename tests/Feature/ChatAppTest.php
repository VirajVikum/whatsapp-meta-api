<?php

namespace Tests\Feature;

use Tests\TestCase;

class ChatAppTest extends TestCase
{
    public function test_chat_page_loads(): void
    {
        $response = $this->get('/chat');
        $response->assertStatus(200);
        $response->assertViewIs('chat');
    }

    public function test_webhook_endpoint_is_accessible(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'ok',
        ]);
    }
}
