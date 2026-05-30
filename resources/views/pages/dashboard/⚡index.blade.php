<?php

use App\Models\Employee;
use App\Models\Leave;
use App\Models\Department;
use App\Enums\RoleType;
use App\Enums\LeaveStateType;
use App\Enums\AccountStateType;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Title('Dashboard')] class extends Component {
    public function stats()
    {
        $user = Auth::user();
        $isRH = $user->hasRole([RoleType::HR->value, RoleType::SUPERADMIN->value]);

        if ($isRH) {
            return [
                'total_employees' => Employee::where('status', AccountStateType::ACTIVE)->count(),
                'pending_leaves' => Leave::where('status', LeaveStateType::PENDING)->count(),
                'on_leave_today' => Leave::where('status', LeaveStateType::APPROVED)
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->count(),
                'upcoming_anniversaries' => Employee::whereRaw("DATE_FORMAT(hireDate, '%m-%d') BETWEEN ? AND ?", [
                    now()->format('m-d'),
                    now()->addDays(30)->format('m-d')
                ])->get(),
                'department_stats' => Department::withCount('employees')->get(),
            ];
        }

        $employee = $user->employee;
        return [
            'leave_balance' => $employee ? $employee->leave_balance : 0,
            'pending_requests' => $employee ? $employee->leaves()->where('status', LeaveStateType::PENDING)->get() : collect(),
            'approved_leaves' => $employee ? $employee->leaves()->where('status', LeaveStateType::APPROVED)->where('start_date', '>=', now())->get() : collect(),
        ];
    }
}; ?>

<x-layouts::app>
    @php $data = $this->stats(); @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <flux:card class="relative overflow-hidden border-0 bg-gradient-to-r from-amber-100 via-white to-zinc-100 p-6 dark:from-amber-500/10 dark:via-zinc-900 dark:to-zinc-900">
            <div class="absolute -right-20 -top-20 size-56 rounded-full bg-amber-200/40 blur-3xl dark:bg-amber-500/20"></div>
            <div class="relative flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <flux:text class="text-xs uppercase tracking-widest text-zinc-500">{{ __('HRBoard Workspace') }}</flux:text>
                    <flux:heading size="xl">{{ __('Welcome back,') }} {{ Auth::user()->name }}</flux:heading>
                    <flux:text color="zinc" class="mt-1">{{ __('Here is your live snapshot for today.') }}</flux:text>
                </div>
                <div class="flex items-center gap-2">
                    @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                        <flux:button variant="ghost" icon="printer" href="{{ route('reports.monthly-leaves.pdf') }}">
                            {{ __('Monthly Leave Report') }}
                        </flux:button>
                        <flux:button variant="primary" icon="users" :href="route('employees.create')" wire:navigate>
                            {{ __('Add Employee') }}
                        </flux:button>
                    @else
                        <flux:button variant="primary" icon="calendar" :href="route('leaves.index')" wire:navigate>
                            {{ __('Request Leave') }}
                        </flux:button>
                        <flux:button variant="ghost" icon="user" :href="route('profile.show')" wire:navigate>
                            {{ __('My Profile') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        </flux:card>

        @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:card class="flex flex-col items-center justify-center p-6 border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('Active Employees') }}</flux:text>
                    <flux:heading size="xl" class="mt-2">{{ $data['total_employees'] }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col items-center justify-center p-6 border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('Pending Leaves') }}</flux:text>
                    <flux:heading size="xl" class="mt-2 text-warning-600">{{ $data['pending_leaves'] }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col items-center justify-center p-6 border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('On Leave Today') }}</flux:text>
                    <flux:heading size="xl" class="mt-2 text-accent-600">{{ $data['on_leave_today'] }}</flux:heading>
                </flux:card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <flux:card class="border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:heading size="lg" class="mb-4">{{ __('Work Anniversaries (Next 30 days)') }}</flux:heading>
                    <flux:table>
                        <flux:rows>
                            @forelse ($data['upcoming_anniversaries'] as $emp)
                                <flux:row>
                                    <flux:cell>
                                        <div class="flex items-center gap-2">
                                            <flux:avatar :name="$emp->full_name" size="xs" />
                                            <span>{{ $emp->full_name }}</span>
                                        </div>
                                    </flux:cell>
                                    <flux:cell>{{ $emp->hireDate->format('d M') }} ({{ now()->year - $emp->hireDate->year }} {{ __('years') }})</flux:cell>
                                </flux:row>
                            @empty
                                <flux:row>
                                    <flux:cell class="text-zinc-500 italic">{{ __('No anniversaries soon.') }}</flux:cell>
                                </flux:row>
                            @endforelse
                        </flux:rows>
                    </flux:table>
                </flux:card>

                <flux:card class="border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:heading size="lg" class="mb-4">{{ __('Department Distribution') }}</flux:heading>
                    <div class="space-y-3">
                        @foreach ($data['department_stats'] as $dept)
                            <div class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <flux:text>{{ $dept->name }}</flux:text>
                                    <flux:text class="font-bold">{{ $dept->employees_count }}</flux:text>
                                </div>
                                <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2">
                                    @php $percent = $data['total_employees'] > 0 ? ($dept->employees_count / $data['total_employees']) * 100 : 0; @endphp
                                    <div class="bg-accent-500 h-2 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:card class="flex flex-col items-center justify-center p-6 border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('Available Leave Balance') }}</flux:text>
                    <flux:heading size="xl" class="mt-2">{{ number_format($data['leave_balance'], 1) }} {{ __('days') }}</flux:heading>
                </flux:card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <flux:card class="border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:heading size="lg" class="mb-4">{{ __('My Pending Requests') }}</flux:heading>
                    <flux:table>
                        <flux:rows>
                            @forelse ($data['pending_requests'] as $leave)
                                <flux:row>
                                    <flux:cell>{{ $leave->type->name }}</flux:cell>
                                    <flux:cell>{{ $leave->start_date->format('d/m/Y') }}</flux:cell>
                                    <flux:cell>{{ $leave->days_count }} {{ __('days') }}</flux:cell>
                                </flux:row>
                            @empty
                                <flux:row>
                                    <flux:cell class="text-zinc-500 italic">{{ __('No pending requests.') }}</flux:cell>
                                </flux:row>
                            @endforelse
                        </flux:rows>
                    </flux:table>
                </flux:card>

                <flux:card class="border-0 bg-white/80 shadow-sm backdrop-blur dark:bg-zinc-900/70">
                    <flux:heading size="lg" class="mb-4">{{ __('My Next Approved Leaves') }}</flux:heading>
                    <flux:table>
                        <flux:rows>
                            @forelse ($data['approved_leaves'] as $leave)
                                <flux:row>
                                    <flux:cell>{{ $leave->type->name }}</flux:cell>
                                    <flux:cell>{{ $leave->start_date->format('d/m/Y') }}</flux:cell>
                                    <flux:cell>{{ $leave->days_count }} {{ __('days') }}</flux:cell>
                                </flux:row>
                            @empty
                                <flux:row>
                                    <flux:cell class="text-zinc-500 italic">{{ __('No upcoming leaves.') }}</flux:cell>
                                </flux:row>
                            @endforelse
                        </flux:rows>
                    </flux:table>
                </flux:card>
            </div>
        @endif
    </div>
</x-layouts::app>
