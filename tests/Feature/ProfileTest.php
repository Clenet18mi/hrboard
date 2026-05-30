<?php

namespace Tests\Feature;

use App\Enums\RoleType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_employee_details(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::EMPLOYEE->value);

        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'department_id' => Department::factory(),
            'jobTitle' => 'HR Analyst',
        ]);

        $response = $this->actingAs($user)->get(route('profile.show'));
        $response->assertOk();
        $response->assertSee($employee->full_name);
        $response->assertSee('HR Analyst');
    }

    public function test_profile_page_handles_missing_employee(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::EMPLOYEE->value);

        $response = $this->actingAs($user)->get(route('profile.show'));
        $response->assertOk();
        $response->assertSee('No employee profile found');
    }
}
