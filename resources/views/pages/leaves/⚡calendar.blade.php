<?php

use App\Models\Leave;
use App\Enums\LeaveStateType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Leave Calendar')] class extends Component {
    public int $month;
    public int $year;

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function calendarDays(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)
            ->startOfMonth()
            ->startOfWeek(Carbon::MONDAY);
        $end = Carbon::create($this->year, $this->month, 1)
            ->endOfMonth()
            ->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $days[] = $current->copy();
            $current->addDay();
        }

        return $days;
    }

    public function leaveMap(): array
    {
        $rangeStart = Carbon::create($this->year, $this->month, 1)
            ->startOfMonth()
            ->startOfWeek(Carbon::MONDAY);
        $rangeEnd = Carbon::create($this->year, $this->month, 1)
            ->endOfMonth()
            ->endOfWeek(Carbon::SUNDAY);

        $leaves = Leave::with('employee.user')
            ->where('status', LeaveStateType::APPROVED)
            ->whereDate('start_date', '<=', $rangeEnd)
            ->whereDate('end_date', '>=', $rangeStart)
            ->get();

        $map = [];
        foreach ($leaves as $leave) {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);
            foreach ($period as $date) {
                $key = $date->toDateString();
                $map[$key][] = $leave;
            }
        }

        return $map;
    }
}; ?>

<x-layouts::app>
    @php
        $monthLabel = Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
        $days = $this->calendarDays();
        $leaveMap = $this->leaveMap();
        $weekdays = [__('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')];
    @endphp

    <div class="flex items-center justify-between pb-4">
        <div>
            <flux:heading size="xl">{{ __('Leave Calendar') }}</flux:heading>
            <flux:subheading>{{ __('Monthly overview of approved leaves.') }}</flux:subheading>
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="ghost" icon="chevron-left" wire:click="previousMonth">{{ __('Previous') }}</flux:button>
            <flux:button variant="ghost" icon="chevron-right" wire:click="nextMonth">{{ __('Next') }}</flux:button>
        </div>
    </div>

    <flux:card class="p-4">
        <div class="flex items-center justify-between pb-3">
            <flux:text class="text-sm uppercase tracking-widest text-zinc-500">{{ $monthLabel }}</flux:text>
            <flux:badge size="sm" variant="neutral" inset="false">{{ __('Approved only') }}</flux:badge>
        </div>

        <div class="grid grid-cols-7 gap-2 text-xs uppercase tracking-widest text-zinc-500 pb-2">
            @foreach ($weekdays as $label)
                <div class="px-2">{{ $label }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-2">
            @foreach ($days as $day)
                @php
                    $isCurrentMonth = $day->month === $this->month;
                    $isToday = $day->isToday();
                    $dateKey = $day->toDateString();
                    $items = $leaveMap[$dateKey] ?? [];
                @endphp
                <div class="min-h-[110px] rounded-xl border border-zinc-200 bg-white/80 p-2 text-xs shadow-sm transition hover:border-amber-300 dark:border-zinc-700 dark:bg-zinc-900/70">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold {{ $isCurrentMonth ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400' }}">
                            {{ $day->format('j') }}
                        </span>
                        @if ($isToday)
                            <span class="rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-semibold text-amber-900">{{ __('Today') }}</span>
                        @endif
                    </div>

                    <div class="mt-2 space-y-1">
                        @forelse ($items as $leave)
                            <div class="rounded-md bg-amber-100/80 px-2 py-1 text-[11px] text-amber-900">
                                <div class="font-semibold">{{ $leave->employee->full_name }}</div>
                                <div class="text-[10px] uppercase tracking-wide">{{ $leave->type_label }}</div>
                            </div>
                        @empty
                            <div class="text-[10px] text-zinc-400">{{ __('No leaves') }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </flux:card>
</x-layouts::app>
