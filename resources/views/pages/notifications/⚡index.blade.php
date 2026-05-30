<?php

use App\Models\Notification;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new #[Title('Notifications')] class extends Component {
    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead()
    {
        Auth::user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function notifications()
    {
        return Auth::user()->notifications()->latest()->paginate(20);
    }
}; ?>

<x-layouts::app>
    <div class="flex items-center justify-between pb-6">
        <flux:heading size="xl">{{ __('Notifications') }}</flux:heading>
        
        @if (Auth::user()->notifications()->whereNull('read_at')->exists())
            <flux:button variant="ghost" wire:click="markAllAsRead">{{ __('Mark all as read') }}</flux:button>
        @endif
    </div>

    <div class="space-y-4">
        @forelse ($this->notifications() as $notification)
            <flux:card :class="$notification->read_at ? 'opacity-60' : 'border-accent-500/30 bg-accent-50/10'">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            @php
                                $icon = match($notification->type) {
                                    'leave_approved' => 'check-circle',
                                    'leave_rejected' => 'x-circle',
                                    'leave_request' => 'calendar',
                                    default => 'bell',
                                };
                            @endphp
                            <flux:icon :icon="$icon" size="sm" class="{{ $notification->read_at ? 'text-zinc-400' : 'text-accent-500' }}" />
                            <flux:text size="sm" color="zinc">{{ $notification->created_at->diffForHumans() }}</flux:text>
                        </div>
                        <flux:text :class="!$notification->read_at ? 'font-medium' : ''">{{ $notification->message }}</flux:text>
                    </div>
                    
                    @if (!$notification->read_at)
                        <flux:button variant="ghost" size="sm" icon="check" wire:click="markAsRead({{ $notification->id }})" />
                    @endif
                </div>
            </flux:card>
        @empty
            <div class="text-center py-12 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl">
                <flux:icon icon="bell" size="lg" class="mx-auto text-zinc-300 mb-2" />
                <flux:text color="zinc">{{ __('You have no notifications.') }}</flux:text>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $this->notifications()->links() }}
    </div>
</x-layouts::app>
