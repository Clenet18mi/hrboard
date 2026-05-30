<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Leave;
use App\Enums\RoleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // Create Super Admin
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@hrboard.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->assignRole(RoleType::SUPERADMIN->value);

        // Create HR Manager
        $hrUser = User::factory()->create([
            'name' => 'HR Manager',
            'email' => 'hr@hrboard.com',
            'password' => Hash::make('password'),
        ]);
        $hrUser->assignRole(RoleType::HR->value);

        // Create Departments
        $departments = Department::factory(5)->create();
        
        // HR Department
        $hrDept = Department::create(['name' => 'Ressources Humaines']);
        
        // Create HR Employee profile
        Employee::factory()->create([
            'user_id' => $hrUser->id,
            'firstName' => 'Jane',
            'lastName' => 'Doe',
            'department_id' => $hrDept->id,
            'jobTitle' => 'HR Manager',
        ]);

        // Create Employees
        Employee::factory(10)->create()->each(function ($employee) {
            $employee->user->assignRole(RoleType::EMPLOYEE->value);
            
            // Create some leaves for each employee
            Leave::factory(3)->create([
                'employee_id' => $employee->id,
            ]);
        });
    }
}
