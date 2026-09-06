<x-layouts::auth :title="__('Sign In - DMMNHS APMS')">
    <div 
        x-data="{ showPassword: false }" 
        class="w-full space-y-6 font-sans text-center"
    >
        <!-- 1. TOP VERTICAL HEADER: SCHOOL BRANDING & LOGO SPACE -->
        @php
            $schoolLogoUrl = \App\Models\SystemSetting::logoUrl();
            $schoolName = \App\Models\SystemSetting::get('school_name', 'Don Mariano Marcos NHS');
            $schoolAddress = \App\Models\SystemSetting::get('school_address', 'Dummun, Gattaran, Cagayan, Philippines');
        @endphp
        <!-- 1. TOP VERTICAL HEADER: SCHOOL BRANDING & LOGO SPACE -->
        <div class="space-y-3">
            <!-- Dedicated Space for School Logo -->
            <div class="inline-block relative group">
                <div class="absolute -inset-1 rounded-3xl bg-gradient-to-r from-[#064e3b] via-[#166534] to-emerald-400 blur-sm opacity-70 group-hover:opacity-100 transition duration-300"></div>
                @if ($schoolLogoUrl)
                    <div class="relative rounded-3xl bg-white border-2 border-white/40 shadow-2xl p-2 mx-auto flex items-center justify-center overflow-hidden shrink-0" style="width: 104px; height: 104px; max-width: 104px; max-height: 104px;">
                        <img src="{{ $schoolLogoUrl }}" alt="{{ $schoolName }} Logo" class="max-w-full max-h-full w-auto h-auto object-contain block" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                @else
                    <div class="relative rounded-3xl bg-gradient-to-br from-[#064e3b] via-[#166534] to-[#0f5127] border-2 border-white/20 flex flex-col items-center justify-center text-white shadow-2xl p-2 mx-auto shrink-0" style="width: 104px; height: 104px; max-width: 104px; max-height: 104px;">
                        <!-- School Logo Crest / Image Slot -->
                        <i class="fas fa-graduation-cap text-3xl sm:text-4xl text-amber-400 mb-1 drop-shadow-md"></i>
                        <span class="text-[9px] font-black uppercase tracking-widest text-emerald-100 font-mono">DMMNHS</span>
                    </div>
                @endif
            </div>

            <div class="space-y-1">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
                    {{ $schoolName }}
                </h1>
                
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/80 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-[11px] font-bold text-[#166534] dark:text-emerald-300 shadow-xs">
                    <i class="fas fa-chart-line text-amber-500"></i>
                    <span>Academic Performance Monitoring System with Analytics</span>
                </div>

                <p class="text-xs text-zinc-500 dark:text-zinc-400 font-medium pt-0.5">
                    {{ $schoolAddress }}
                </p>
            </div>
        </div>

        <!-- 2. MIDDLE VERTICAL LOGIN FORM CARD -->
        <div class="bg-white dark:bg-zinc-900 p-7 sm:p-8 rounded-3xl border border-zinc-200/80 dark:border-zinc-800 shadow-2xl space-y-6 text-start relative overflow-hidden">
            <!-- Top Subtle Card Accent Line -->
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#064e3b] via-[#166534] to-emerald-400"></div>

            <!-- Form Card Title -->
            <div class="border-b border-zinc-100 dark:border-zinc-800/80 pb-4">
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2.5">
                    <div class="size-8 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-[#166534] dark:text-emerald-400 flex items-center justify-center text-sm shadow-inner">
                        <i class="fas fa-right-to-bracket"></i>
                    </div>
                    <span>Account Sign In</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-1 font-medium">Please enter your assigned Account ID and password to access the portal.</p>
            </div>

            <!-- Session Status Alert -->
            <x-auth-session-status class="text-center" :status="session('status')" />

            <!-- Error Banner -->
            @if ($errors->any())
                <div class="p-3.5 rounded-xl bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800/80 text-xs text-red-700 dark:text-red-300 space-y-1 animate-fade-in">
                    <div class="flex items-center gap-2 font-bold">
                        <i class="fas fa-circle-exclamation text-red-500"></i>
                        <span>Sign In Failed</span>
                    </div>
                    <p class="text-[11px] text-red-600 dark:text-red-400">
                        {{ $errors->first('email') ?? $errors->first('login') ?? $errors->first('password') ?? 'These credentials do not match our records.' }}
                    </p>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                @csrf

                <!-- Account ID Input Field -->
                <div>
                    <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1.5 uppercase tracking-wider">Account ID / User Number *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 text-xs">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <input 
                            type="text" 
                            name="email" 
                            value="{{ old('email') }}"
                            required 
                            autofocus 
                            placeholder="0000-0000" 
                            class="w-full pl-10 pr-4 py-3 text-xs font-semibold font-mono rounded-xl border @error('email') border-red-500 @else border-zinc-300 dark:border-zinc-700 @enderror bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:bg-white focus:dark:bg-zinc-800 focus:ring-2 focus:ring-[#166534] focus:border-transparent transition shadow-xs"
                        >
                    </div>
                    @error('email')
                        <p class="text-[11px] text-red-600 dark:text-red-400 font-medium mt-1 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle text-[10px]"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Password Input Field with Interactive Eye Toggle -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">Password *</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400 text-xs">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input 
                            :type="showPassword ? 'text' : 'password'" 
                            name="password" 
                            required 
                            placeholder="Password" 
                            class="w-full pl-10 pr-10 py-3 text-xs font-semibold rounded-xl border @error('password') border-red-500 @else border-zinc-300 dark:border-zinc-700 @enderror bg-zinc-50/50 dark:bg-zinc-800/50 text-zinc-900 dark:text-zinc-100 focus:bg-white focus:dark:bg-zinc-800 focus:ring-2 focus:ring-[#166534] focus:border-transparent transition shadow-xs"
                        >
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition cursor-pointer"
                            tabindex="-1"
                        >
                            <i class="fas" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-[11px] text-red-600 dark:text-red-400 font-medium mt-1 flex items-center gap-1">
                            <i class="fas fa-exclamation-circle text-[10px]"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Keep Me Signed In -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-medium text-zinc-600 dark:text-zinc-400 select-none">
                        <input type="checkbox" name="remember" class="rounded border-zinc-300 dark:border-zinc-700 text-[#166534] focus:ring-[#166534] size-4">
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Sign In Button -->
                <button 
                    type="submit" 
                    class="w-full py-3.5 px-5 bg-gradient-to-r from-[#064e3b] via-[#166534] to-emerald-700 hover:from-[#166534] hover:to-emerald-800 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer group"
                >
                    <span>Sign In to System</span>
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>

        <!-- 3. BOTTOM VERTICAL FOOTER -->
        <div class="text-[11px] text-zinc-500 dark:text-zinc-400 font-medium pt-2">
            <p>© {{ date('Y') }} Don Mariano Marcos National High School</p>
        </div>
    </div>
</x-layouts::auth>
