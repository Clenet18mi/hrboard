<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
use App\Models\User;

class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'message' => $this->faker->sentence(),
            'type' => $this->faker->randomElement(['leave_approved', 'leave_rejected', 'new_leave_request']),
            'read_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
