<?php

namespace Tests\Feature;

use App\Enums\LeaveStateType;
use App\Enums\LeaveType;
use App\Enums\RoleType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeavesTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_open_leave_calendar(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::HR->value);

        $response = $this->actingAs($user)->get(route('leaves.calendar'));
        $response->assertOk();
        $response->assertSee('Leave Calendar');
    }

    public function test_employee_cannot_open_leave_calendar(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::EMPLOYEE->value);

        $response = $this->actingAs($user)->get(route('leaves.calendar'));
        $response->assertForbidden();
    }

    public function test_paid_leave_request_is_blocked_when_balance_is_insufficient(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::EMPLOYEE->value);

        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'department_id' => Department::factory(),
            'hireDate' => now()->subMonth(),
        ]);

        Livewire::actingAs($user)
            ->test('pages::leaves.index')
            ->set('type', LeaveType::PAID->value)
            ->set('start_date', now()->addDay()->format('Y-m-d'))
            ->set('end_date', now()->addDays(10)->format('Y-m-d'))
            ->call('submitRequest')
            ->assertHasErrors(['start_date']);

        $this->assertSame(0, Leave::count());
    }

    public function test_hr_can_filter_pending_requests(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole(RoleType::HR->value);

        $employee = Employee::factory()->create([
            'department_id' => Department::factory(),
        ]);

        $pendingLeave = Leave::factory()->create([
            'employee_id' => $employee->id,
            'status' => LeaveStateType::PENDING->value,
        ]);

        Leave::factory()->create([
            'employee_id' => $employee->id,
            'status' => LeaveStateType::APPROVED->value,
        ]);

        Livewire::actingAs($user)
            ->test('pages::leaves.index')
            ->set('statusFilter', 'pending')
            ->assertSee((string) $pendingLeave->days_count)
            ->assertSee($employee->full_name);
    }
}
