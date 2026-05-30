<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Enums\AccountStateType;
use App\Enums\ContractType;
use App\Enums\RoleType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Flux\Flux;

new #[Title('Employee')] class extends Component {
    use WithFileUploads;

    public ?Employee $employee = null;
    
    // User fields
    public string $name = '';
    public string $email = '';
    public string $password = '';

    // Employee fields
    public string $firstName = '';
    public string $lastName = '';
    public string $phone = '';
    public $birthDate;
    public $hireDate;
    public string $gender = 'male';
    public string $jobTitle = '';
    public ?int $department_id = null;
    public string $contractType = 'cdi';
    public $grossSalary;
    public string $status = 'active';
    public $photo;

    public function mount(?Employee $employee = null)
    {
        if ($employee && $employee->exists) {
            $this->employee = $employee;
            $this->name = $employee->user->name;
            $this->email = $employee->user->email;
            
            $this->firstName = $employee->firstName;
            $this->lastName = $employee->lastName;
            $this->phone = $employee->phone ?? '';
            $this->birthDate = $employee->birthDate?->format('Y-m-d');
            $this->hireDate = $employee->hireDate?->format('Y-m-d');
            $this->gender = $employee->gender;
            $this->jobTitle = $employee->jobTitle;
            $this->department_id = $employee->department_id;
            $this->contractType = $employee->contractType->value;
            $this->grossSalary = $employee->grossSalary;
            $this->status = $employee->status->value;
        } else {
            $this->hireDate = now()->format('Y-m-d');
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($this->employee ? $this->employee->user_id : 'NULL'),
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'jobTitle' => 'required|string|max:255',
            'hireDate' => 'required|date',
            'contractType' => 'required|string',
            'status' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'grossSalary' => 'nullable|numeric',
            'phone' => 'nullable|string|max:20',
            'birthDate' => 'nullable|date',
            'gender' => 'required|in:male,female',
        ];

        if (!$this->employee) {
            $rules['password'] = 'required|string|min:8';
        }

        $validated = $this->validate($rules);

        if ($this->employee) {
            $this->employee->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $this->employee->update(collect($validated)->except(['name', 'email', 'password'])->toArray());
            
            Flux::toast(variant: 'success', text: __('Employee updated.'));
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $user->assignRole(RoleType::EMPLOYEE->value);

            Employee::create(array_merge(
                collect($validated)->except(['name', 'email', 'password'])->toArray(),
                ['user_id' => $user->id]
            ));

            Flux::toast(variant: 'success', text: __('Employee created.'));
        }

        return $this->redirect(route('employees.index'), navigate: true);
    }

    public function departments()
    {
        return Department::all();
    }

    public function contractTypes()
    {
        return ContractType::cases();
    }

    public function statuses()
    {
        return AccountStateType::cases();
    }
}; ?>

<x-layouts::app>
    <div class="pb-4">
        <flux:heading size="xl">{{ $employee ? __('Edit Employee') : __('Add Employee') }}</flux:heading>
        <flux:subheading>{{ __('Manage employee profile and contract information.') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-8">
        <flux:card>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <flux:field>
                    <flux:label>{{ __('Full Name (Account)') }}</flux:label>
                    <flux:input wire:model="name" placeholder="John Doe" required />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Email (Account)') }}</flux:label>
                    <flux:input wire:model="email" type="email" placeholder="john.doe@company.com" required />
                </flux:field>

                @if (!$employee)
                    <flux:field>
                        <flux:label>{{ __('Password') }}</flux:label>
                        <flux:input wire:model="password" type="password" required />
                    </flux:field>
                @endif
            </div>
        </flux:card>

        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg">{{ __('Profile Information') }}</flux:heading>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model="firstName" :label="__('First Name')" required />
                    <flux:input wire:model="lastName" :label="__('Last Name')" required />
                    
                    <flux:input wire:model="phone" :label="__('Phone')" />
                    <flux:input wire:model="birthDate" type="date" :label="__('Birth Date')" />
                    
                    <flux:select wire:model="gender" :label="__('Gender')">
                        <flux:select.option value="male">{{ __('Male') }}</flux:select.option>
                        <flux:select.option value="female">{{ __('Female') }}</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="space-y-6">
                <flux:heading size="lg">{{ __('Job & Contract') }}</flux:heading>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <flux:input wire:model="jobTitle" :label="__('Job Title')" required />
                    
                    <flux:select wire:model="department_id" :label="__('Department')">
                        <flux:select.option :value="null">{{ __('Select a department') }}</flux:select.option>
                        @foreach ($this->departments() as $department)
                            <flux:select.option :value="$department->id">{{ $department->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="hireDate" type="date" :label="__('Hire Date')" required />
                    
                    <flux:select wire:model="contractType" :label="__('Contract Type')">
                        @foreach ($this->contractTypes() as $type)
                            <flux:select.option :value="$type->value">{{ $type->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="grossSalary" type="number" step="0.01" :label="__('Gross Monthly Salary')" />
                    
                    <flux:select wire:model="status" :label="__('Status')">
                        @foreach ($this->statuses() as $status)
                            <flux:select.option :value="$status->value">{{ $status->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </flux:card>

        <div class="flex justify-end gap-4">
            <flux:button variant="ghost" :href="route('employees.index')" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" type="submit">{{ __('Save Employee') }}</flux:button>
        </div>
    </form>
</x-layouts::app>
