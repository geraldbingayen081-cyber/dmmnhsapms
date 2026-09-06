@props(['title' => 'Admin Module', 'subtitle' => 'Academic Performance Monitoring System'])

<header class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
    <div class="flex items-center gap-4">
        <!-- Mobile Sidebar Toggle -->
        <button id="admin-mobile-toggle" class="lg:hidden p-2 text-zinc-600 hover:text-zinc-900 rounded-lg hover:bg-zinc-100">
            <i class="fas fa-bars text-lg"></i>
        </button>

        <div>
            <h1 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 tracking-tight leading-tight">
                {{ $title }}
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium">
                {{ $subtitle }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-4 text-xs font-medium">
        <!-- Active School Year Indicator -->
        @php
            $activeSy = \App\Services\AcademicContextService::getActiveSchoolYear();
        @endphp
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#DCFCE7] text-[#166534] border border-emerald-200 font-bold">
            <i class="fas fa-calendar-check"></i>
            <span>School Year: {{ $activeSy ? $activeSy->school_year : 'None Active' }}</span>
        </div>

        <!-- Admin Profile Pill Dropdown -->
        <div class="flex items-center gap-3 border-l border-zinc-200 dark:border-zinc-800 pl-4">
            <div class="text-end hidden md:block">
                <span class="block font-bold text-zinc-900 dark:text-zinc-100 text-xs">{{ auth()->user()->name }}</span>
                <span class="block text-[10px] text-[#166534] font-mono font-bold uppercase">{{ auth()->user()->login_id }}</span>
            </div>
            <div class="size-9 rounded-xl bg-[#166534] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                {{ auth()->user()->initials() }}
            </div>
        </div>
    </div>
</header>
