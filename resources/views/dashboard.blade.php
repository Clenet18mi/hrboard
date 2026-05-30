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
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Welcome back,') }} {{ Auth::user()->name }}</flux:heading>
            @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                <flux:button variant="ghost" icon="printer" href="{{ route('reports.monthly-leaves.pdf') }}">{{ __('Monthly Leave Report') }}</flux:button>
            @endif
        </div>

        @if (Auth::user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
            {{-- RH / Admin Dashboard --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:card class="flex flex-col items-center justify-center p-6">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('Active Employees') }}</flux:text>
                    <flux:heading size="xl" class="mt-2">{{ $data['total_employees'] }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col items-center justify-center p-6">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('Pending Leaves') }}</flux:text>
                    <flux:heading size="xl" class="mt-2 text-warning-600">{{ $data['pending_leaves'] }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col items-center justify-center p-6">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('On Leave Today') }}</flux:text>
                    <flux:heading size="xl" class="mt-2 text-accent-600">{{ $data['on_leave_today'] }}</flux:heading>
                </flux:card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <flux:card>
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

                <flux:card>
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
            {{-- Employee Dashboard --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <flux:card class="flex flex-col items-center justify-center p-6">
                    <flux:text color="zinc" class="uppercase text-xs font-bold tracking-wider">{{ __('Available Leave Balance') }}</flux:text>
                    <flux:heading size="xl" class="mt-2">{{ number_format($data['leave_balance'], 1) }} {{ __('days') }}</flux:heading>
                </flux:card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <flux:card>
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

                <flux:card>
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
