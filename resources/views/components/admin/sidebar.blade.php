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
                <i class="fas fa-graduation-cap"></i>
            </div>
        @endif
        <div class="min-w-0">
            <h1 class="font-bold text-sm leading-tight text-white tracking-wide uppercase truncate">DMMNHS APMS</h1>
            <p class="text-[10px] text-emerald-200 font-medium truncate">Administrator Console</p>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6 text-xs font-medium scrollbar-thin scrollbar-thumb-emerald-800">
        <!-- Overview & Core System Section -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Core System</span>
            
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400 shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-gauge-high w-4 text-center text-amber-300"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.achievements') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.achievements*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400 shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-award w-4 text-center text-amber-400"></i>
                <span>Student Achievements</span>
            </a>
        </div>

        <!-- Academic Structure -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Academic Structure</span>
            
            <a href="{{ route('admin.school-years') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.school-years*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-calendar-days w-4 text-center"></i>
                <span>School Years</span>
            </a>

            <!--<a href="{{ route('admin.quarters') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.quarters*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-list-ol w-4 text-center"></i>
                <span>Quarter Periods</span>
            </a>

            <a href="{{ route('admin.year-levels') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.year-levels*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-layer-group w-4 text-center"></i>
                <span>Year Levels (G7-G10)</span>
            </a>-->

            <a href="{{ route('admin.sections') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.sections') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-users-rectangle w-4 text-center"></i>
                <span>Sections</span>
            </a>

            <a href="{{ route('admin.subjects') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.subjects*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-book w-4 text-center"></i>
                <span>Subjects</span>
            </a>
        </div>

        <!-- Users & Accounts -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">User Directory</span>
            
            <a href="{{ route('admin.students') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.students*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-user-graduate w-4 text-center"></i>
                <span>Students Directory</span>
            </a>

            <a href="{{ route('admin.teachers') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.teachers*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-chalkboard-user w-4 text-center"></i>
                <span>Teachers Directory</span>
            </a>

            <a href="{{ route('admin.parents') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.parents*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-user-group w-4 text-center"></i>
                <span>Parents & Linker</span>
            </a>
        </div>

        <!-- Academic Operations -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Academic Operations</span>
            
            <a href="{{ route('admin.advisers') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.advisers*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-shield-halved w-4 text-center"></i>
                <span>Class Advisers</span>
            </a>

            <a href="{{ route('admin.teacher-assignments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.teacher-assignments*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-clipboard-user w-4 text-center"></i>
                <span>Subject Teacher Loads</span>
            </a>

            <a href="{{ route('admin.enrollments') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.enrollments*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-arrows-split-up-and-left w-4 text-center"></i>
                <span>Enrollment & Promotion</span>
            </a>

            <a href="{{ route('admin.grades') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.grades*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-file-lines w-4 text-center"></i>
                <span>Grade Monitoring</span>
            </a>
        </div>

        <!-- Intelligence & Outputs -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Settings</span>
            
            <!-- <a href="{{ route('admin.analytics') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.analytics*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-chart-column w-4 text-center"></i>
                <span>Academic Analytics</span>
            </a>-->

           <!--  <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.reports*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-file-pdf w-4 text-center"></i>
                <span>DepEd Reports (SF9/10)</span>
            </a>-->

            <a href="{{ route('admin.activity-logs') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.activity-logs*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-clock-rotate-left w-4 text-center"></i>
                <span>System Audit Logs</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.settings*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-gear w-4 text-center"></i>
                <span>System Settings</span>
            </a>
        </div>
    </nav>

    <!-- Admin Footer Account Summary -->
    <div class="p-3 border-t border-emerald-800/60 bg-emerald-950/40 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2.5 truncate">
            <div class="w-8 h-8 rounded-full bg-amber-400 text-emerald-950 font-bold flex items-center justify-center text-xs shadow-sm">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="truncate">
                <span class="font-bold text-white block truncate leading-tight">{{ auth()->user()->name ?? 'Administrator' }}</span>
                <span class="text-[10px] text-emerald-300 font-mono block">{{ auth()->user()->login_id ?? 'Admin-0001' }}</span>
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
