<?php

use App\Models\Employee;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Employee Profile')] class extends Component {
    public Employee $employee;

    public function mount(Employee $employee)
    {
        $this->employee = $employee->load('user', 'department', 'leaves');
    }
}; ?>

<x-layouts::app>
    <div class="flex items-center justify-between pb-6">
        <div class="flex items-center gap-4">
            <flux:avatar :name="$employee->full_name" :src="$employee->photo ? asset('storage/' . $employee->photo) : null" size="xl" />
            <div>
                <flux:heading size="xl">{{ $employee->full_name }}</flux:heading>
                <flux:subheading>{{ $employee->jobTitle }} • {{ $employee->department?->name ?? __('No Department') }}</flux:subheading>
            </div>
        </div>
        
        <div class="flex gap-2">
            <flux:dropdown>
                <flux:button variant="ghost" icon="printer" icon-trailing="chevron-down">{{ __('Generate PDF') }}</flux:button>
                <flux:menu>
                    <flux:menu.item href="{{ route('employees.pdf', $employee->id) }}">{{ __('Employee Sheet') }}</flux:menu.item>
                    <flux:menu.item href="{{ route('employees.attestation', $employee->id) }}">{{ __('Work Certificate') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
            <flux:button variant="ghost" icon="pencil" :href="route('employees.edit', $employee->id)" wire:navigate>{{ __('Edit') }}</flux:button>
            <flux:button variant="ghost" icon="arrow-left" :href="route('employees.index')" wire:navigate>{{ __('Back to list') }}</flux:button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Personal Information') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Email') }}</flux:text>
                        <flux:text>{{ $employee->user->email }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Phone') }}</flux:text>
                        <flux:text>{{ $employee->phone ?? __('N/A') }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Birth Date') }}</flux:text>
                        <flux:text>{{ $employee->birthDate?->format('d/m/Y') ?? __('N/A') }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Gender') }}</flux:text>
                        <flux:text>{{ ucfirst($employee->gender) }}</flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Contract Details') }}</flux:heading>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Job Title') }}</flux:text>
                        <flux:text>{{ $employee->jobTitle }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Department') }}</flux:text>
                        <flux:text>{{ $employee->department?->name ?? __('N/A') }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Hire Date') }}</flux:text>
                        <flux:text>{{ $employee->hireDate->format('d/m/Y') }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Contract Type') }}</flux:text>
                        <flux:text>{{ strtoupper($employee->contractType->value) }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Gross Monthly Salary') }}</flux:text>
                        <flux:text>{{ $employee->grossSalary ? number_format($employee->grossSalary, 2) . ' €' : __('N/A') }}</flux:text>
                    </div>
                    <div>
                        <flux:text color="zinc" class="block">{{ __('Status') }}</flux:text>
                        <flux:badge size="sm" :inset="false">{{ $employee->status->name }}</flux:badge>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Recent Leave History') }}</flux:heading>
                <flux:table>
                    <flux:columns>
                        <flux:column>{{ __('Type') }}</flux:column>
                        <flux:column>{{ __('Period') }}</flux:column>
                        <flux:column>{{ __('Days') }}</flux:column>
                        <flux:column>{{ __('Status') }}</flux:column>
                    </flux:columns>
                    <flux:rows>
                        @forelse ($employee->leaves()->latest()->take(5)->get() as $leave)
                            <flux:row :key="$leave->id">
                                <flux:cell>{{ $leave->type->name }}</flux:cell>
                                <flux:cell>{{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }}</flux:cell>
                                <flux:cell>{{ $leave->days_count }}</flux:cell>
                                <flux:cell>
                                    @php
                                        $variant = match($leave->status) {
                                            App\Enums\LeaveStateType::APPROVED => 'success',
                                            App\Enums\LeaveStateType::REJECTED => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <flux:badge size="sm" :variant="$variant" :inset="false">{{ $leave->status->name }}</flux:badge>
                                </flux:cell>
                            </flux:row>
                        @empty
                            <flux:row>
                                <flux:cell colspan="4" class="text-center text-zinc-500 py-4">{{ __('No leave history found.') }}</flux:cell>
                            </flux:row>
                        @endforelse
                    </flux:rows>
                </flux:table>
            </flux:card>
        </div>

        <div class="space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('Statistics') }}</flux:heading>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <flux:text color="zinc">{{ __('Leave Balance (Paid)') }}</flux:text>
                        <flux:text class="font-bold">{{ number_format($employee->leave_balance, 1) }} {{ __('days') }}</flux:text>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <flux:text color="zinc">{{ __('Approved Leaves') }}</flux:text>
                        <flux:text class="font-bold">{{ $employee->leaves()->where('status', App\Enums\LeaveStateType::APPROVED)->count() }}</flux:text>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <flux:text color="zinc">{{ __('Total Days Taken') }}</flux:text>
                        <flux:text class="font-bold">{{ $employee->leaves()->where('status', App\Enums\LeaveStateType::APPROVED)->sum('days_count') }}</flux:text>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <flux:text color="zinc">{{ __('Seniority') }}</flux:text>
                        <flux:text class="font-bold">{{ $employee->hireDate->diffInMonths(now()) }} {{ __('months') }}</flux:text>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
