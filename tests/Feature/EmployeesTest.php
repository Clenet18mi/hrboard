<?php

namespace Tests\Feature;

use App\Enums\ContractType;
use App\Enums\RoleType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_filter_employees_by_contract_type(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::HR->value);

        $department = Department::factory()->create();

        $cdiEmployee = Employee::factory()->create([
            'department_id' => $department->id,
            'contractType' => ContractType::CDI->value,
        ]);

        $cddEmployee = Employee::factory()->create([
            'department_id' => $department->id,
            'contractType' => ContractType::CDD->value,
        ]);

        Livewire::actingAs($user)
            ->test('pages::employees.index')
            ->set('contract_type', ContractType::CDI->value)
            ->assertSee($cdiEmployee->full_name)
            ->assertDontSee($cddEmployee->full_name);
    }
}
