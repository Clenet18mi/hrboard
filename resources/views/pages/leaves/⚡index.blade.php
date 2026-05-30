<?php

use App\Models\Leave;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Leaves')] class extends Component {
    //
}; ?>

<x-layouts::app>
    <flux:heading size="xl">{{ __('Leave Requests') }}</flux:heading>
    <flux:subheading>{{ __('Manage employee leave requests.') }}</flux:subheading>
    
    <div class="mt-8 flex items-center justify-center h-64 border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl">
        <flux:text color="zinc">{{ __('Leaves management coming soon...') }}</flux:text>
    </div>
</x-layouts::app>
