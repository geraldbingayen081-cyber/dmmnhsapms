@props(['title' => 'Student Portal', 'subtitle' => 'Academic Performance Monitoring System'])

<header class="bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-sm">
    <div class="flex items-center gap-4">
        <!-- Mobile Sidebar Toggle -->
        <button id="student-mobile-toggle" class="lg:hidden p-2 text-zinc-600 hover:text-zinc-900 rounded-lg hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800">
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

    <div class="flex items-center gap-3 sm:gap-4 text-xs font-medium">
        <!-- Active School Year & Quarter Indicator (Q1 - Q3) -->
        @php
            $activeSy = \App\Services\AcademicContextService::getActiveSchoolYear();
            $currentQuarter = $activeSy ? \App\Services\AcademicContextService::getCurrentQuarter($activeSy->id) : null;
            $student = auth()->user()->student;
            $activeEnr = $student && $activeSy ? $student->enrollments()->whereHas('schoolYearSection', fn($q) => $q->where('school_year_id', $activeSy->id))->with(['schoolYearSection.yearLevel', 'schoolYearSection.section'])->first() : null;
        @endphp
        
        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#DCFCE7] text-[#166534] border border-emerald-200 font-bold">
            <i class="fas fa-calendar-check"></i>
            <span>SY {{ $activeSy ? $activeSy->school_year : '2026-2027' }}</span>
            @if ($currentQuarter)
                <span class="text-emerald-400">•</span>
                <span>{{ $currentQuarter->short_name }}</span>
            @endif
        </div>

        @if ($activeEnr)
            <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 font-bold text-[11px]">
                <i class="fas fa-user-graduate text-emerald-600 dark:text-emerald-400"></i>
                <span>{{ $activeEnr->schoolYearSection?->yearLevel?->name }} - {{ $activeEnr->schoolYearSection?->section?->name }}</span>
            </div>
        @endif

        <!-- Student Profile Pill -->
        <div class="flex items-center gap-3 border-l border-zinc-200 dark:border-zinc-800 pl-3 sm:pl-4">
            <div class="text-end hidden md:block">
                <span class="block font-bold text-zinc-900 dark:text-zinc-100 text-xs">{{ auth()->user()->name }}</span>
                <span class="block text-[10px] text-[#166534] dark:text-emerald-400 font-mono font-bold uppercase">{{ auth()->user()->login_id }}</span>
            </div>
            <div class="size-9 rounded-xl bg-[#166534] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                {{ auth()->user()->initials() }}
            </div>
        </div>
    </div>
</header>
