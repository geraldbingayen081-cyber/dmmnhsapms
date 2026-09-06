<aside class="w-64 bg-[#166534] text-white flex flex-col h-screen sticky top-0 font-sans shadow-lg select-none z-30">
    @php
        $schoolLogoUrl = \App\Models\SystemSetting::logoUrl();
    @endphp
    <!-- Header / Branding Area -->
    <div class="p-4 border-b border-emerald-800/60 flex items-center gap-3">
        @if ($schoolLogoUrl)
            <div class="w-10 h-10 rounded-xl bg-white p-1 flex items-center justify-center border border-white/20 shadow-sm shrink-0 overflow-hidden" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px; max-height: 40px;">
                <img src="{{ $schoolLogoUrl }}" alt="Logo" class="max-w-full max-h-full w-auto h-auto object-contain block" style="max-width: 100%; max-height: 100%; object-fit: contain;">
            </div>
        @else
            <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-sm flex items-center justify-center text-amber-400 font-black text-lg border border-white/20 shadow-inner shrink-0" style="width: 40px; height: 40px; min-width: 40px; max-width: 40px; max-height: 40px;">
                <i class="fas fa-chalkboard-user"></i>
            </div>
        @endif
        <div class="min-w-0">
            <h1 class="font-bold text-sm leading-tight text-white tracking-wide uppercase truncate">DMMNHS APMS</h1>
            <p class="text-[10px] text-emerald-200 font-medium truncate">Faculty Portal • Junior High</p>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6 text-xs font-medium scrollbar-thin scrollbar-thumb-emerald-800">
        <!-- Overview Section -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Main</span>
            
            <a href="{{ route('teacher.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('teacher.dashboard') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400 shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-gauge-high w-4 text-center text-amber-300"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Academic Loads Section -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Academic Loads</span>
            
            <a href="{{ route('teacher.classes') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('teacher.classes*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-layer-group w-4 text-center"></i>
                <span>My Subject Classes</span>
            </a>

            <a href="{{ route('teacher.grades') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('teacher.grades*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-file-pen w-4 text-center"></i>
                <span>Grade Encoding Sheet</span>
            </a>
        </div>

        <!-- Class Advisory Section -->
        <div class="space-y-1">
            <div class="flex items-center justify-between px-3 mb-1">
                <span class="text-[10px] font-bold text-emerald-300 uppercase tracking-widest block">Class Advisory</span>
                @php
                    $teacherModel = auth()->user()->teacher;
                    $activeSy = \App\Services\AcademicContextService::getActiveSchoolYear();
                    $isAdviser = $teacherModel && $activeSy ? $teacherModel->advisedSections()->where('school_year_id', $activeSy->id)->exists() : false;
                @endphp
                @if ($isAdviser)
                    <span class="text-[9px] font-extrabold bg-amber-400 text-emerald-950 px-1.5 py-0.5 rounded-full uppercase">Adviser</span>
                @endif
            </div>
            
            <a href="{{ route('teacher.advisory') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('teacher.advisory*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-shield-halved w-4 text-center text-amber-400"></i>
                <span>My Advisee Section</span>
            </a>

           <!--   <a href="{{ route('teacher.reports') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('teacher.reports*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-file-pdf w-4 text-center"></i>
               <span>DepEd Reports (SF9)</span>-->
            </a>
        </div>

        <!-- Parents & Students Section -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Parents & Students</span>
            
            <a href="{{ route('teacher.parents') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('teacher.parents*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-user-group w-4 text-center text-amber-300"></i>
                <span>Parent Accounts & Linker</span>
            </a>

            <a href="{{ route('teacher.students') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('teacher.students*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-user-graduate w-4 text-center"></i>
                <span>Student Directory</span>
            </a>
        </div>
    </nav>

    <!-- Teacher Footer Account Summary -->
    <div class="p-3 border-t border-emerald-800/60 bg-emerald-950/40 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2.5 truncate">
            <div class="w-8 h-8 rounded-full bg-amber-400 text-emerald-950 font-bold flex items-center justify-center text-xs shadow-sm">
                {{ substr(auth()->user()->name ?? 'T', 0, 1) }}
            </div>
            <div class="truncate">
                <span class="font-bold text-white block truncate leading-tight">{{ auth()->user()->name ?? 'Faculty Member' }}</span>
                <span class="text-[10px] text-emerald-300 font-mono block">{{ auth()->user()->login_id ?? 'EMP-0000' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Logout" class="p-1.5 text-emerald-200 hover:text-white hover:bg-white/10 rounded-lg transition">
                <i class="fas fa-right-from-bracket text-xs"></i>
            </button>
        </form>
    </div>
</aside>
