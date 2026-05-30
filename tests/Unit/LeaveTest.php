<?php

namespace Tests\Unit;

use App\Enums\LeaveStateType;
use App\Enums\LeaveType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_days_excludes_weekends(): void
    {
        $days = Leave::calculateDays('2026-05-30', '2026-06-02');
        $this->assertSame(2, $days);
    }

    public function test_overlapping_scope_detects_overlaps(): void
    {
        $employee = Employee::factory()->create([
            'department_id' => Department::factory(),
        ]);

        Leave::factory()->create([
            'employee_id' => $employee->id,
            'type' => LeaveType::PAID->value,
            'status' => LeaveStateType::APPROVED->value,
            'start_date' => '2026-06-10',
            'end_date' => '2026-06-12',
            'days_count' => 3,
        ]);

        $overlapExists = Leave::overlapping($employee->id, '2026-06-11', '2026-06-13')->exists();
        $noOverlapExists = Leave::overlapping($employee->id, '2026-06-14', '2026-06-15')->exists();

        $this->assertTrue($overlapExists);
        $this->assertFalse($noOverlapExists);
    }
}
