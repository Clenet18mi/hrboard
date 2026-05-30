<?php

use App\Models\Employee;
use App\Models\Department;
use App\Enums\AccountStateType;
use App\Enums\ContractType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

new #[Title('Employees')] class extends Component {
    use WithPagination;

    public string $search = '';
    public ?int $department_id = null;
    public ?string $status = null;

    public function delete(Employee $employee)
    {
        $employee->delete();
        Flux::toast(variant: 'success', text: __('Employee archived.'));
    }

    public function employees()
    {
        return Employee::query()
            ->with('department', 'user')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('firstName', 'like', '%' . $this->search . '%')
                      ->orWhere('lastName', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($qu) {
                          $qu->where('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->department_id, fn($q) => $q->where('department_id', $this->department_id))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->paginate(10);
    }

    public function departments()
    {
        return Department::all();
    }

    public function statuses()
    {
        return AccountStateType::cases();
    }
}; ?>

<x-layouts::app>
    <div class="flex items-center justify-between pb-4">
        <flux:heading size="xl">{{ __('Employees') }}</flux:heading>
        
        <flux:button variant="primary" icon="plus" :href="route('employees.create')" wire:navigate>
            {{ __('Add Employee') }}
        </flux:button>
    </div>

    <div class="flex gap-4 pb-4">
        <div class="flex-1">
            <flux:input wire:model.live="search" placeholder="{{ __('Search by name or email...') }}" icon="magnifying-glass" />
        </div>
        <div class="w-64">
            <flux:select wire:model.live="department_id" placeholder="{{ __('All Departments') }}">
                <flux:select.option :value="null">{{ __('All Departments') }}</flux:select.option>
                @foreach ($this->departments() as $department)
                    <flux:select.option :value="$department->id">{{ $department->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
        <div class="w-48">
            <flux:select wire:model.live="status" placeholder="{{ __('All Statuses') }}">
                <flux:select.option :value="null">{{ __('All Statuses') }}</flux:select.option>
                @foreach ($this->statuses() as $status)
                    <flux:select.option :value="$status->value">{{ $status->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <flux:table>
        <flux:columns>
            <flux:column>{{ __('Name') }}</flux:column>
            <flux:column>{{ __('Email') }}</flux:column>
            <flux:column>{{ __('Department') }}</flux:column>
            <flux:column>{{ __('Status') }}</flux:column>
            <flux:column>{{ __('Hire Date') }}</flux:column>
            <flux:column align="end">{{ __('Actions') }}</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->employees() as $employee)
                <flux:row :key="$employee->id">
                    <flux:cell>
                        <div class="flex items-center gap-2">
                            <flux:avatar :name="$employee->full_name" :src="$employee->photo ? asset('storage/' . $employee->photo) : null" size="sm" />
                            <flux:link :href="route('employees.show', $employee->id)" wire:navigate class="font-medium">{{ $employee->full_name }}</flux:link>
                        </div>
                    </flux:cell>
                    <flux:cell>{{ $employee->user->email }}</flux:cell>
                    <flux:cell>{{ $employee->department?->name ?? __('N/A') }}</flux:cell>
                    <flux:cell>
                        <flux:badge size="sm" :inset="false">{{ $employee->status->name }}</flux:badge>
                    </flux:cell>
                    <flux:cell>{{ $employee->hireDate->format('d/m/Y') }}</flux:cell>
                    <flux:cell align="end">
                        <flux:button variant="ghost" icon="pencil" :href="route('employees.edit', $employee->id)" wire:navigate />
                        <flux:button variant="ghost" icon="trash" wire:click="delete({{ $employee->id }})" wire:confirm="{{ __('Are you sure you want to archive this employee?') }}" />
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <div class="mt-4">
        {{ $this->employees()->links() }}
    </div>
</x-layouts::app>
