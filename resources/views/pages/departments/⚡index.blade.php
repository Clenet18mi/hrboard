<?php

use App\Models\Department;
use App\Models\Employee;
use Livewire\Attributes\Title;
use Livewire\Component;
use Flux\Flux;

new #[Title('Departments')] class extends Component {
    public string $name = '';
    public ?int $manager_id = null;
    public ?Department $editing = null;

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'nullable|exists:employees,id',
        ]);

        if ($this->editing) {
            $this->editing->update($validated);
            Flux::toast(variant: 'success', text: __('Department updated.'));
        } else {
            Department::create($validated);
            Flux::toast(variant: 'success', text: __('Department created.'));
        }

        $this->reset(['name', 'manager_id', 'editing']);
        $this->dispatch('close-modal', name: 'department-modal');
    }

    public function edit(Department $department)
    {
        $this->editing = $department;
        $this->name = $department->name;
        $this->manager_id = $department->manager_id;
        $this->dispatch('open-modal', name: 'department-modal');
    }

    public function delete(Department $department)
    {
        if ($department->employees()->count() > 0) {
            Flux::toast(variant: 'danger', text: __('Cannot delete department with employees.'));
            return;
        }

        $department->delete();
        Flux::toast(variant: 'success', text: __('Department deleted.'));
    }

    public function cancel()
    {
        $this->reset(['name', 'manager_id', 'editing']);
    }

    public function departments()
    {
        return Department::with('manager')->get();
    }

    public function employees()
    {
        return Employee::all();
    }
}; ?>

<x-layouts::app>
    <div x-data x-on:close-modal.window="$flux.modal($event.detail.name).close()" x-on:open-modal.window="$flux.modal($event.detail.name).show()">
        <div class="flex items-center justify-between pb-4">
        <flux:heading size="xl">{{ __('Departments') }}</flux:heading>
        
        <flux:modal.trigger name="department-modal">
            <flux:button variant="primary" icon="plus">{{ __('Add Department') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table>
        <flux:columns>
            <flux:column>{{ __('Name') }}</flux:column>
            <flux:column>{{ __('Manager') }}</flux:column>
            <flux:column>{{ __('Employees Count') }}</flux:column>
            <flux:column align="end">{{ __('Actions') }}</flux:column>
        </flux:columns>

        <flux:rows>
            @foreach ($this->departments() as $department)
                <flux:row :key="$department->id">
                    <flux:cell class="font-medium">{{ $department->name }}</flux:cell>
                    <flux:cell>{{ $department->manager?->full_name ?? __('N/A') }}</flux:cell>
                    <flux:cell>{{ $department->employees()->count() }}</flux:cell>
                    <flux:cell align="end">
                        <flux:button variant="ghost" icon="printer" href="{{ route('reports.department.pdf', $department->id) }}" />
                        <flux:button variant="ghost" icon="pencil" wire:click="edit({{ $department->id }})" />
                        <flux:button variant="ghost" icon="trash" wire:click="delete({{ $department->id }})" />
                    </flux:cell>
                </flux:row>
            @endforeach
        </flux:rows>
    </flux:table>

    <flux:modal name="department-modal" class="md:w-[400px]">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">{{ $editing ? __('Edit Department') : __('Add Department') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" required />

            <flux:select wire:model="manager_id" :label="__('Manager')" placeholder="{{ __('Select a manager') }}">
                <flux:select.option :value="null">{{ __('None') }}</flux:select.option>
                @foreach ($this->employees() as $employee)
                    <flux:select.option :value="$employee->id">{{ $employee->full_name }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex">
                <flux:spacer />
                <flux:button variant="ghost" wire:click="cancel" class="mr-2">{{ __('Cancel') }}</flux:button>
                <flux:button variant="primary" type="submit">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
    </div>
</x-layouts::app>
