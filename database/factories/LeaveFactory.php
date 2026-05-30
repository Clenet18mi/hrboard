<?php

namespace Database\Factories;

use App\Models\Leave;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Leave>
 */
use App\Models\Employee;
use App\Models\User;
use App\Enums\LeaveType;
use App\Enums\LeaveStateType;

class LeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $endDate = (clone $startDate)->modify('+' . rand(1, 10) . ' days');
        
        return [
            'employee_id' => Employee::factory(),
            'type' => $this->faker->randomElement(LeaveType::cases()),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_count' => $startDate->diff($endDate)->days + 1,
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(LeaveStateType::cases()),
            'hr_comment' => $this->faker->optional()->sentence(),
            'approved_by' => null,
        ];
    }
}
