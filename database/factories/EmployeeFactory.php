<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
use App\Models\User;
use App\Models\Department;
use App\Enums\ContractType;
use App\Enums\AccountStateType;

class EmployeeFactory extends Factory
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
            'firstName' => $this->faker->firstName(),
            'lastName' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'birthDate' => $this->faker->date('Y-m-d', '-20 years'),
            'hireDate' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female']),
            'jobTitle' => $this->faker->jobTitle(),
            'department_id' => Department::factory(),
            'contractType' => $this->faker->randomElement(ContractType::cases()),
            'grossSalary' => $this->faker->randomFloat(2, 2000, 5000),
            'status' => AccountStateType::ACTIVE,
            'photo' => null,
        ];
    }
}
