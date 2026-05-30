<?php

use App\Models\Leave;
use App\Models\Employee;
use App\Enums\LeaveType;
use App\Enums\LeaveStateType;
use App\Enums\RoleType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

new #[Title('Leaves')] class extends Component {
    use WithPagination;

    public bool $showRequestModal = false;
    
    // Request fields
    public string $type = 'paid';
    public $start_date;
    public $end_date;
    public string $reason = '';

    // RH Review fields
    public ?Leave $reviewingLeave = null;
    public string $hr_comment = '';

    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->format('Y-m-d');
    }

    public function submitRequest()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            Flux::toast(variant: 'danger', text: __('You must be an employee to request leave.'));
            return;
        }

        $validated = $this->validate([
            'type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        // Check for overlaps
        if (Leave::overlapping($employee->id, $this->start_date, $this->end_date)->exists()) {
            $this->addError('start_date', __('You already have an approved leave during this period.'));
            return;
        }

        $daysCount = Leave::calculateDays($this->start_date, $this->end_date);

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'type' => $this->type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days_count' => $daysCount,
            'reason' => $this->reason,
            'status' => LeaveStateType::PENDING,
        ]);

        NotificationService::notifyRH(
            __('New leave request from :name', ['name' => $employee->full_name]),
            'leave_request'
        );

        Flux::toast(variant: 'success', text: __('Leave request submitted.'));
        $this->reset(['type', 'reason']);
        $this->dispatch('close-modal', name: 'request-modal');
    }

    public function review(Leave $leave)
    {
        $this->reviewingLeave = $leave;
        $this->hr_comment = $leave->hr_comment ?? '';
        $this->dispatch('open-modal', name: 'review-modal');
    }

    public function approve()
    {
        $this->reviewingLeave->update([
            'status' => LeaveStateType::APPROVED,
            'hr_comment' => $this->hr_comment,
            'approved_by' => Auth::id(),
        ]);

        NotificationService::notify(
            $this->reviewingLeave->employee->user,
            __('Your leave request from :start to :end has been approved.', [
                'start' => $this->reviewingLeave->start_date->format('d/m/Y'),
                'end' => $this->reviewingLeave->end_date->format('d/m/Y')
            ]),
            'leave_approved'
        );

        Flux::toast(variant: 'success', text: __('Leave request approved.'));
        $this->dispatch('close-modal', name: 'review-modal');
    }

    public function reject()
    {
        $this->reviewingLeave->update([
            'status' => LeaveStateType::REJECTED,
            'hr_comment' => $this->hr_comment,
            'approved_by' => Auth::id(),
        ]);

        NotificationService::notify(
            $this->reviewingLeave->employee->user,
            __('Your leave request from :start to :end has been rejected.', [
                'start' => $this->reviewingLeave->start_date->format('d/m/Y'),
                'end' => $this->reviewingLeave->end_date->format('d/m/Y')
            ]),
            'leave_rejected'
        );

        Flux::toast(variant: 'success', text: __('Leave request rejected.'));
        $this->dispatch('close-modal', name: 'review-modal');
    }

    public function leaves()
    {
        $query = Leave::with('employee.user', 'approver');

        if (!Auth::user()->hasRole([RoleType::HR->value, RoleType::SUPERADMIN->value])) {
            $query->where('employee_id', Auth::user()->employee?->id);
        }

        return $query->latest()->paginate(10);
    }

    public function leaveTypes()
    {
        return LeaveType::cases();
    }
}; ?>

<x-layouts::app>
    <div x-data x-on:close-modal.window="$flux.modal($event.detail.name).close()" x-on:open-modal.window="$flux.modal($event.detail.name).show()">
        <div class="flex items-center justify-between pb-4">
            <flux:heading size="xl">{{ __('Leave Requests') }}</flux:heading>
            
            @if (Auth::user()->employee)
                <flux:modal.trigger name="request-modal">
                    <flux:button variant="primary" icon="plus">{{ __('Request Leave') }}</flux:button>
                </flux:modal.trigger>
            @endif
        </div>

        <flux:table>
            <flux:columns>
                @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                    <flux:column>{{ __('Employee') }}</flux:column>
                @endif
                <flux:column>{{ __('Type') }}</flux:column>
                <flux:column>{{ __('Period') }}</flux:column>
                <flux:column>{{ __('Days') }}</flux:column>
                <flux:column>{{ __('Status') }}</flux:column>
                <flux:column align="end">{{ __('Actions') }}</flux:column>
            </flux:columns>

            <flux:rows>
                @foreach ($this->leaves() as $leave)
                    <flux:row :key="$leave->id">
                        @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                            <flux:cell>{{ $leave->employee->full_name }}</flux:cell>
                        @endif
                        <flux:cell>{{ $leave->type->name }}</flux:cell>
                        <flux:cell>
                            {{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }}
                        </flux:cell>
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
                        <flux:cell align="end">
                            @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]) && $leave->status === App\Enums\LeaveStateType::PENDING)
                                <flux:button variant="ghost" icon="check-circle" wire:click="review({{ $leave->id }})" />
                            @endif
                            <flux:modal.trigger name="detail-modal-{{ $leave->id }}">
                                <flux:button variant="ghost" icon="eye" />
                            </flux:modal.trigger>
                        </flux:cell>
                    </flux:row>

                    {{-- Detail Modal for each leave --}}
                    <flux:modal name="detail-modal-{{ $leave->id }}" class="md:w-[500px]">
                        <div class="space-y-6">
                            <flux:heading size="lg">{{ __('Leave Details') }}</flux:heading>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="font-medium">{{ __('Employee') }}:</div>
                                <div>{{ $leave->employee->full_name }}</div>
                                
                                <div class="font-medium">{{ __('Type') }}:</div>
                                <div>{{ $leave->type->name }}</div>
                                
                                <div class="font-medium">{{ __('Period') }}:</div>
                                <div>{{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }} ({{ $leave->days_count }} {{ __('days') }})</div>
                                
                                <div class="font-medium">{{ __('Reason') }}:</div>
                                <div class="italic text-zinc-500">{{ $leave->reason ?? __('No reason provided') }}</div>
                                
                                <div class="font-medium">{{ __('Status') }}:</div>
                                <div><flux:badge size="sm" :variant="$variant" :inset="false">{{ $leave->status->name }}</flux:badge></div>
                                
                                @if ($leave->hr_comment)
                                    <div class="font-medium">{{ __('HR Comment') }}:</div>
                                    <div class="text-zinc-500">{{ $leave->hr_comment }}</div>
                                @endif
                                
                                @if ($leave->approver)
                                    <div class="font-medium">{{ __('Reviewed by') }}:</div>
                                    <div>{{ $leave->approver->name }}</div>
                                @endif
                            </div>

                            <div class="flex">
                                <flux:spacer />
                                <flux:modal.close>
                                    <flux:button variant="ghost">{{ __('Close') }}</flux:button>
                                </flux:modal.close>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach
            </flux:rows>
        </flux:table>

        <div class="mt-4">
            {{ $this->leaves()->links() }}
        </div>

        {{-- Request Leave Modal --}}
        <flux:modal name="request-modal" class="md:w-[450px]">
            <form wire:submit="submitRequest" class="space-y-6">
                <flux:heading size="lg">{{ __('Request Leave') }}</flux:heading>

                <flux:select wire:model="type" :label="__('Leave Type')">
                    @foreach ($this->leaveTypes() as $lt)
                        <flux:select.option :value="$lt->value">{{ $lt->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="date" wire:model="start_date" :label="__('Start Date')" required />
                    <flux:input type="date" wire:model="end_date" :label="__('End Date')" required />
                </div>

                <flux:textarea wire:model="reason" :label="__('Reason (Optional)')" placeholder="{{ __('Why do you need this leave?') }}" />

                <div class="flex">
                    <flux:spacer />
                    <flux:button variant="ghost" class="mr-2" x-on:click="$flux.modal('request-modal').close()">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ __('Submit Request') }}</flux:button>
                </div>
            </form>
        </flux:modal>

        {{-- Review Leave Modal (RH only) --}}
        <flux:modal name="review-modal" class="md:w-[450px]">
            <form class="space-y-6">
                <flux:heading size="lg">{{ __('Review Leave Request') }}</flux:heading>

                @if ($reviewingLeave)
                    <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg text-sm space-y-2">
                        <p><strong>{{ __('Employee') }}:</strong> {{ $reviewingLeave->employee->full_name }}</p>
                        <p><strong>{{ __('Period') }}:</strong> {{ $reviewingLeave->start_date->format('d/m/Y') }} - {{ $reviewingLeave->end_date->format('d/m/Y') }}</p>
                        <p><strong>{{ __('Reason') }}:</strong> {{ $reviewingLeave->reason ?? __('N/A') }}</p>
                    </div>
                @endif

                <flux:textarea wire:model="hr_comment" :label="__('HR Comment (Optional)')" placeholder="{{ __('Add a comment for the employee...') }}" />

                <div class="flex gap-2">
                    <flux:button variant="danger" wire:click="reject" class="flex-1">{{ __('Reject') }}</flux:button>
                    <flux:button variant="primary" wire:click="approve" class="flex-1">{{ __('Approve') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</x-layouts::app>
