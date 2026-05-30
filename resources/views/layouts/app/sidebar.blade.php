<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-amber-50 via-white to-zinc-100 text-zinc-900 dark:from-zinc-950 dark:via-zinc-900 dark:to-zinc-800">
        <div class="min-h-screen lg:flex">
            <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white/80 backdrop-blur dark:border-zinc-700 dark:bg-zinc-900/80">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    @if (auth()->user()->employee)
                        <flux:sidebar.item icon="user" :href="route('profile.show')" :current="request()->routeIs('profile.show')" wire:navigate>
                            {{ __('My Profile') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if (auth()->user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                <flux:sidebar.group :heading="__('Management')" class="grid">
                    <flux:sidebar.item icon="users" :href="route('employees.index')" :current="request()->routeIs('employees.*')" wire:navigate>
                        {{ __('Employees') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-office" :href="route('departments.index')" :current="request()->routeIs('departments.*')" wire:navigate>
                        {{ __('Departments') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif

                <flux:sidebar.group :heading="__('Leaves')" class="grid">
                    <flux:sidebar.item icon="calendar" :href="route('leaves.index')" :current="request()->routeIs('leaves.*')" wire:navigate>
                        {{ __('Leave Requests') }}
                    </flux:sidebar.item>
                    @if (auth()->user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                        <flux:sidebar.item icon="calendar-days" :href="route('leaves.calendar')" :current="request()->routeIs('leaves.calendar')" wire:navigate>
                            {{ __('Leave Calendar') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Account')" class="grid">
                    <flux:sidebar.item icon="bell" :href="route('notifications.index')" :current="request()->routeIs('notifications.*')" wire:navigate>
                        {{ __('Notifications') }}
                        @if ($count = auth()->user()->notifications()->whereNull('read_at')->count())
                            <flux:badge size="sm" variant="danger" class="ml-auto">{{ $count }}</flux:badge>
                        @endif
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                @if (auth()->user()->hasRole([App\Enums\RoleType::HR->value, App\Enums\RoleType::SUPERADMIN->value]))
                    <flux:sidebar.item icon="printer" href="{{ route('reports.monthly-leaves.pdf') }}">
                        {{ __('Monthly Leave Report') }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
            </flux:sidebar>

            <div class="flex-1">
                <!-- Mobile User Menu -->
                <flux:header class="lg:hidden">
                    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                    <flux:spacer />

                    <flux:dropdown position="top" align="end">
                        <div class="relative">
                            <flux:profile
                                :initials="auth()->user()->initials()"
                                icon-trailing="chevron-down"
                            />
                            @if ($count = auth()->user()->notifications()->whereNull('read_at')->count())
                                <div class="absolute -top-1 -right-1 size-4 bg-red-500 rounded-full flex items-center justify-center text-[10px] text-white border-2 border-white dark:border-zinc-900">
                                    {{ $count }}
                                </div>
                            @endif
                        </div>

                        <flux:menu>
                            <flux:menu.radio.group>
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                        <flux:avatar
                                            :name="auth()->user()->name"
                                            :initials="auth()->user()->initials()"
                                        />

                                        <div class="grid flex-1 text-start text-sm leading-tight">
                                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                        </div>
                                    </div>
                                </div>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('notifications.index')" icon="bell" wire:navigate>
                                    {{ __('Notifications') }}
                                </flux:menu.item>
                                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                                    {{ __('Settings') }}
                                </flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                                <flux:menu.item
                                    as="button"
                                    type="submit"
                                    icon="arrow-right-start-on-rectangle"
                                    class="w-full cursor-pointer"
                                    data-test="logout-button"
                                >
                                    {{ __('Log out') }}
                                </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </flux:header>

                {{ $slot }}

                @persist('toast')
                    <flux:toast.group>
                        <flux:toast />
                    </flux:toast.group>
                @endpersist
            </div>
        </div>

        @fluxScripts
    </body>
</html>
