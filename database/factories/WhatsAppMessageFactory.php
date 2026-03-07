<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WhatsAppMessage>
 */
class WhatsAppMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wa_message_id' => 'wamid.'.$this->faker->uuid(),
            'from_phone' => $this->faker->phoneNumber(),
            'to_phone' => $this->faker->phoneNumber(),
            'direction' => $this->faker->randomElement(['incoming', 'outgoing']),
            'message_type' => 'text',
            'body' => $this->faker->sentence(),
            'status' => 'delivered',
            'payload' => [],
        ];
    }
}
