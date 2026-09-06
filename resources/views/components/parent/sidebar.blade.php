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
                <i class="fas fa-hands-holding-child"></i>
            </div>
        @endif
        <div class="min-w-0">
            <h1 class="font-bold text-sm leading-tight text-white tracking-wide uppercase truncate">DMMNHS APMS</h1>
            <p class="text-[10px] text-emerald-200 font-medium truncate">Parent & Guardian Portal</p>
        </div>
    </div>

    <!-- Navigation Menu Items -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-6 text-xs font-medium scrollbar-thin scrollbar-thumb-emerald-800">
        <!-- Overview Section -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Main</span>
            
            <a href="{{ route('parent.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('parent.dashboard') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400 shadow-sm' : 'text-emerald-100 hover:bg-white/10 hover:text-white' }}">
                <i class="fas fa-gauge-high w-4 text-center text-amber-300"></i>
                <span>Children & Dashboard</span>
            </a>
        </div>

        <!-- Academic Records Section -->
        <div class="space-y-1">
            <span class="px-3 text-[10px] font-bold text-emerald-300 uppercase tracking-widest block mb-1">Academic Records</span>
            
            <a href="{{ route('parent.grades') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('parent.grades*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-file-lines w-4 text-center"></i>
                <span>Report Card (Q1–Q3)</span>
            </a>

            <a href="{{ route('parent.achievements') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition {{ request()->routeIs('parent.achievements*') ? 'bg-white/15 text-white font-bold border-l-4 border-amber-400' : 'text-emerald-100 hover:bg-white/10' }}">
                <i class="fas fa-award w-4 text-center text-amber-300"></i>
                <span>Awards and Achievements</span>
            </a>
        </div>

        <!-- Linked Students Summary Strip -->
        <div class="p-3.5 mx-1 rounded-xl bg-emerald-950/40 border border-emerald-800/60 space-y-2 text-[11px]">
            @php
                $parentModel = auth()->user()->parent;
                $children = $parentModel ? $parentModel->students()->with(['enrollments.schoolYearSection.yearLevel', 'enrollments.schoolYearSection.section'])->get() : collect();
            @endphp
            <div class="flex items-center justify-between text-emerald-300 font-bold text-[10px] uppercase">
                <span>My Enrolled Children</span>
                <span class="px-1.5 py-0.5 rounded bg-amber-400 text-emerald-950 font-black text-[9px]">{{ $children->count() }}</span>
            </div>

            @forelse ($children as $c)
                <div class="space-y-0.5 border-t border-emerald-800/50 pt-1.5 first:border-0 first:pt-0">
                    <span class="font-bold text-white block truncate text-[11px]">
                        {{ $c->full_name }}
                    </span>
                    @php
                        $latestEnr = $c->enrollments->last();
                    @endphp
                    <span class="text-emerald-200 block text-[10px]">
                        {{ $latestEnr?->schoolYearSection?->yearLevel?->name ?? 'Junior High' }} - {{ $latestEnr?->schoolYearSection?->section?->name ?? 'Enrolled' }}
                    </span>
                </div>
            @empty
                <span class="text-emerald-300 text-[10px] italic">No linked students found. Contact school admin to link student records.</span>
            @endforelse
        </div>
    </nav>

    <!-- Parent Footer Account Summary -->
    <div class="p-3 border-t border-emerald-800/60 bg-emerald-950/40 flex items-center justify-between text-xs">
        <div class="flex items-center gap-2.5 truncate">
            <div class="w-8 h-8 rounded-full bg-amber-400 text-emerald-950 font-bold flex items-center justify-center text-xs shadow-sm">
                {{ substr(auth()->user()->name ?? 'P', 0, 1) }}
            </div>
            <div class="truncate">
                <span class="font-bold text-white block truncate leading-tight">{{ auth()->user()->name ?? 'Parent' }}</span>
                <span class="text-[10px] text-emerald-300 font-mono block">{{ auth()->user()->login_id ?? 'PARENT-0000' }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Logout" class="p-1.5 text-emerald-200 hover:text-white hover:bg-white/10 rounded-lg transition cursor-pointer">
                <i class="fas fa-right-from-bracket text-xs"></i>
            </button>
        </form>
    </div>
</aside>
