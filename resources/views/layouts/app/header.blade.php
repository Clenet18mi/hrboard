<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-gradient-to-r from-zinc-50 via-white to-amber-50/60 dark:border-zinc-700 dark:from-zinc-900 dark:via-zinc-900 dark:to-amber-500/10">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                @if (auth()->user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                    <flux:navbar.item icon="users" :href="route('employees.index')" :current="request()->routeIs('employees.*')" wire:navigate>
                        {{ __('Employees') }}
                    </flux:navbar.item>
                    <flux:navbar.item icon="building-office" :href="route('departments.index')" :current="request()->routeIs('departments.*')" wire:navigate>
                        {{ __('Departments') }}
                    </flux:navbar.item>
                @endif
                <flux:navbar.item icon="calendar" :href="route('leaves.index')" :current="request()->routeIs('leaves.*')" wire:navigate>
                    {{ __('Leaves') }}
                </flux:navbar.item>
                @if (auth()->user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                    <flux:navbar.item icon="calendar-days" :href="route('leaves.calendar')" :current="request()->routeIs('leaves.calendar')" wire:navigate>
                        {{ __('Calendar') }}
                    </flux:navbar.item>
                @endif
                <flux:navbar.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>
                    {{ __('Notifications') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                @if (auth()->user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                    <flux:navbar.item class="!h-10" icon="sparkles" :href="route('employees.create')" wire:navigate>
                        {{ __('New Employee') }}
                    </flux:navbar.item>
                @endif
                @if (auth()->user()->employee)
                    <flux:navbar.item class="!h-10" icon="plus" :href="route('leaves.index')" wire:navigate>
                        {{ __('Request Leave') }}
                    </flux:navbar.item>
                @endif
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>
        </flux:sidebar>

        {{ $slot }}

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
