<?php

use App\Enums\LeaveStateType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Title('My Profile')] class extends Component {
    public function employee()
    {
        return Auth::user()->employee?->load('department', 'leaves');
    }
}; ?>

<x-layouts::app>
    @php $employee = $this->employee(); @endphp

    <div class="flex items-center justify-between pb-6">
        <div>
            <flux:heading size="xl">{{ __('My Profile') }}</flux:heading>
            <flux:subheading>{{ __('Your personal details and HR overview.') }}</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button variant="ghost" icon="cog" :href="route('profile.edit')" wire:navigate>{{ __('Account Settings') }}</flux:button>
            <flux:button variant="primary" icon="calendar" :href="route('leaves.index')" wire:navigate>{{ __('My Leave Requests') }}</flux:button>
        </div>
    </div>

    @if (!$employee)
        <flux:card class="p-6">
            <flux:heading size="lg">{{ __('No employee profile found') }}</flux:heading>
            <flux:text color="zinc" class="mt-2">{{ __('Please contact HR to complete your employee profile.') }}</flux:text>
        </flux:card>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <flux:card class="lg:col-span-2">
                <div class="flex items-center gap-4">
                    <flux:avatar :name="$employee->full_name" :src="$employee->photo ? asset('storage/' . $employee->photo) : null" size="xl" />
                    <div>
                        <flux:heading size="lg">{{ $employee->full_name }}</flux:heading>
                        <flux:text color="zinc">{{ $employee->jobTitle }} • {{ $employee->department?->name ?? __('No Department') }}</flux:text>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-6">
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
                        <flux:text color="zinc" class="block">{{ __('Hire Date') }}</flux:text>
                        <flux:text>{{ $employee->hireDate->format('d/m/Y') }}</flux:text>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <flux:heading size="lg" class="mb-4">{{ __('My Leave Summary') }}</flux:heading>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <flux:text color="zinc">{{ __('Paid leave balance') }}</flux:text>
                        <flux:text class="font-semibold">{{ number_format($employee->leave_balance, 1) }} {{ __('days') }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text color="zinc">{{ __('Pending requests') }}</flux:text>
                        <flux:text class="font-semibold">{{ $employee->leaves()->where('status', LeaveStateType::PENDING)->count() }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between">
                        <flux:text color="zinc">{{ __('Approved leaves') }}</flux:text>
                        <flux:text class="font-semibold">{{ $employee->leaves()->where('status', LeaveStateType::APPROVED)->count() }}</flux:text>
                    </div>
                </div>
            </flux:card>
        </div>

        <flux:card class="mt-6">
            <flux:heading size="lg" class="mb-4">{{ __('Recent Leave History') }}</flux:heading>
            <flux:table>
                <flux:columns>
                    <flux:column>{{ __('Type') }}</flux:column>
                    <flux:column>{{ __('Period') }}</flux:column>
                    <flux:column>{{ __('Days') }}</flux:column>
                    <flux:column>{{ __('Status') }}</flux:column>
                </flux:columns>
                <flux:rows>
                    @forelse ($employee->leaves()->latest()->take(8)->get() as $leave)
                        <flux:row :key="$leave->id">
                            <flux:cell>{{ $leave->type_label }}</flux:cell>
                            <flux:cell>{{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }}</flux:cell>
                            <flux:cell>{{ $leave->days_count }}</flux:cell>
                            <flux:cell>{{ $leave->status_label }}</flux:cell>
                        </flux:row>
                    @empty
                        <flux:row>
                            <flux:cell colspan="4" class="text-center text-zinc-500 py-4">{{ __('No leave history found.') }}</flux:cell>
                        </flux:row>
                    @endforelse
                </flux:rows>
            </flux:table>
        </flux:card>
    @endif
</x-layouts::app>
