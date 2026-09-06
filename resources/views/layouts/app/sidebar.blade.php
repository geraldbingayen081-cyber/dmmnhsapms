<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 font-sans text-zinc-900 dark:text-zinc-100 antialiased">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <!-- Header with School Crest & Brand -->
            <flux:sidebar.header class="p-4 border-b border-zinc-200 dark:border-zinc-800 bg-[#166534] text-white">
                <div class="flex items-center gap-3">
                    @php $appLogo = \App\Models\SystemSetting::logoUrl(); @endphp
                    @if ($appLogo)
                        <div class="w-9 h-9 rounded-xl bg-white p-0.5 flex items-center justify-center shadow-sm overflow-hidden shrink-0" style="width: 36px; height: 36px; min-width: 36px; max-width: 36px; max-height: 36px;">
                            <img src="{{ $appLogo }}" alt="Logo" class="max-w-full max-h-full w-auto h-auto object-contain block" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    @else
                        <div class="size-9 rounded-xl bg-white/10 flex items-center justify-center font-bold text-white shadow-sm shrink-0">
                            <i class="fas fa-graduation-cap text-lg"></i>
                        </div>
                    @endif
                    <div class="grid leading-tight">
                        <span class="font-bold text-xs tracking-tight text-white">DMMNHS APMS</span>
                        <span class="text-[10px] text-emerald-200 font-medium">Academic Portal</span>
                    </div>
                </div>
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="p-3 space-y-1">
                <flux:sidebar.group :heading="__('Academic Operations')" class="grid gap-1">
                    <flux:sidebar.item icon="chart-bar" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate class="rounded-xl font-medium text-xs">
                        {{ __('Analytics Dashboard') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="academic-cap" :href="route('students')" :current="request()->routeIs('students')" wire:navigate class="rounded-xl font-medium text-xs">
                        {{ __('Student Directory') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="pencil-square" :href="route('grades')" :current="request()->routeIs('grades')" wire:navigate class="rounded-xl font-medium text-xs">
                        {{ __('Grade Management') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item icon="trophy" :href="route('achievements')" :current="request()->routeIs('achievements')" wire:navigate class="rounded-xl font-medium text-xs">
                        {{ __('Academic Achievements') }}
                    </flux:sidebar.item>

                    @if(auth()->check() && (auth()->user()->isParent() || auth()->user()->isAdmin()))
                        <flux:sidebar.item icon="users" :href="route('parent')" :current="request()->routeIs('parent')" wire:navigate class="rounded-xl font-medium text-xs">
                            {{ __('Parent Portal') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>

                @if(auth()->check() && auth()->user()->isAdmin())
                    <flux:sidebar.group :heading="__('Administration')" class="grid gap-1 mt-4">
                        <flux:sidebar.item icon="calendar" :href="route('school-years')" :current="request()->routeIs('school-years')" wire:navigate class="rounded-xl font-medium text-xs">
                            {{ __('School Year Setup') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="user-plus" :href="route('user-management')" :current="request()->routeIs('user-management')" wire:navigate class="rounded-xl font-medium text-xs">
                            {{ __('User & Parent Linker') }}
                        </flux:sidebar.item>

                        <flux:sidebar.item icon="briefcase" :href="route('teacher-assignments')" :current="request()->routeIs('teacher-assignments')" wire:navigate class="rounded-xl font-medium text-xs">
                            {{ __('Teacher Workloads') }}
                        </flux:sidebar.item>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <!-- User Info Pill Card -->
            <div class="p-3 m-2 rounded-xl bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700/80">
                <div class="flex items-center gap-3">
                    <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                    <div class="grid flex-1 text-start leading-tight">
                        <span class="truncate font-bold text-xs text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</span>
                        <span class="truncate font-mono text-[10px] text-[#166534] dark:text-emerald-400 font-bold uppercase">{{ auth()->user()->login_id }}</span>
                    </div>
                </div>
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                <flux:menu>
                    <div class="px-4 py-2.5 text-xs font-medium border-b border-zinc-100 dark:border-zinc-800">
                        <div class="font-bold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-[#166534] font-mono font-bold">{{ auth()->user()->login_id }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
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

        @fluxScripts
    </body>
</html>
