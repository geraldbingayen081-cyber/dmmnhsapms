<div class="space-y-6 font-sans">
    <!-- Header & Backup Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div>
            <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-gear text-[#166534]"></i>
                <span>System Settings & School Configuration</span>
            </h2>
            <p class="text-xs text-zinc-500 mt-0.5">Manage institutional profile, official school logo, academic grading thresholds, database maintenance, and security credentials.</p>
        </div>

        <button wire:click="triggerDatabaseBackup" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-database"></i>
            <span>Download DB Backup (.SQL)</span>
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-[#DCFCE7] text-[#166534] border border-emerald-300 text-xs font-bold flex items-center gap-2 shadow-xs">
            <i class="fas fa-circle-check text-base"></i>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 rounded-xl bg-rose-50 text-rose-700 border border-rose-300 text-xs font-bold flex items-center gap-2 shadow-xs">
            <i class="fas fa-circle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 text-xs">
        <!-- 1. INSTITUTIONAL SCHOOL PROFILE & OFFICIAL LOGO -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-5">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <i class="fas fa-school text-[#166534]"></i>
                <span>Institutional School Profile & Official Logo</span>
            </h3>

            <form wire:submit.prevent="saveSchoolProfile" class="space-y-4">
                <!-- School Logo Upload & Preview Field -->
                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700/80 space-y-3">
                    <label class="block font-bold text-zinc-900 dark:text-zinc-100 text-xs">
                        Official School Logo
                    </label>

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <!-- Logo Preview Avatar -->
                        <div class="relative group shrink-0" style="width: 88px; height: 88px;">
                            @if ($logo)
                                <!-- Live Temporary Preview of newly chosen file -->
                                <div class="w-22 h-22 rounded-2xl bg-white border-2 border-emerald-500 shadow-md p-1.5 flex items-center justify-center overflow-hidden shrink-0" style="width: 88px; height: 88px; max-width: 88px; max-height: 88px;">
                                    <img src="{{ $logo->temporaryUrl() }}" alt="New Logo Preview" class="max-w-full max-h-full w-auto h-auto object-contain block" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;">
                                </div>
                                <span class="absolute -top-2 -right-2 px-1.5 py-0.5 rounded-full bg-emerald-600 text-white text-[9px] font-black uppercase shadow z-10">New</span>
                            @elseif ($current_logo_url)
                                <!-- Currently Saved Logo -->
                                <div class="w-22 h-22 rounded-2xl bg-white border-2 border-emerald-600 shadow-md p-1.5 flex items-center justify-center overflow-hidden shrink-0" style="width: 88px; height: 88px; max-width: 88px; max-height: 88px;">
                                    <img src="{{ $current_logo_url }}" alt="School Logo" class="max-w-full max-h-full w-auto h-auto object-contain block" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;">
                                </div>
                                <span class="absolute -top-2 -right-2 px-1.5 py-0.5 rounded-full bg-[#166534] text-white text-[9px] font-black uppercase shadow z-10">Active</span>
                            @else
                                <!-- Default Crest Icon -->
                                <div class="w-22 h-22 rounded-2xl bg-[#166534] text-white border-2 border-emerald-700/60 shadow-md flex flex-col items-center justify-center p-2 shrink-0" style="width: 88px; height: 88px; max-width: 88px; max-height: 88px;">
                                    <i class="fas fa-graduation-cap text-3xl text-amber-400 mb-0.5"></i>
                                    <span class="text-[8px] font-black tracking-widest text-emerald-200 font-mono">DEFAULT</span>
                                </div>
                            @endif
                        </div>

                        <!-- Upload Controls & Guidance -->
                        <div class="space-y-2 flex-1 w-full">
                            <input 
                                type="file" 
                                id="school-logo-input"
                                wire:model="logo" 
                                accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml"
                                class="w-full text-xs text-zinc-600 dark:text-zinc-300 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#166534] file:text-white hover:file:bg-emerald-800 file:cursor-pointer cursor-pointer border border-zinc-300 dark:border-zinc-700 rounded-lg p-1 bg-white dark:bg-zinc-800"
                            >

                            <div wire:loading wire:target="logo" class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1.5">
                                <i class="fas fa-circle-notch fa-spin"></i>
                                <span>Uploading & processing image preview...</span>
                            </div>

                            @error('logo') 
                                <span class="text-rose-600 text-[11px] font-bold block">{{ $message }}</span> 
                            @enderror

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                <p class="text-[10px] text-zinc-500">
                                    Formats: PNG, JPG, WEBP, SVG (Max: 3MB). Replaces logo on Login & all user dashboards.
                                </p>

                                @if ($current_logo_url)
                                    <button 
                                        type="button" 
                                        wire:click="removeLogo" 
                                        wire:confirm="Are you sure you want to remove the custom school logo and revert to the default system crest?"
                                        class="text-[10px] text-rose-600 hover:text-rose-700 font-bold hover:underline inline-flex items-center gap-1 cursor-pointer"
                                    >
                                        <i class="fas fa-trash-can"></i>
                                        <span>Remove Logo</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Official School Name *</label>
                    <input type="text" wire:model.defer="school_name" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    @error('school_name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">School Address *</label>
                    <input type="text" wire:model.defer="school_address" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    @error('school_address') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">DepEd Division & Region *</label>
                    <input type="text" wire:model.defer="school_division" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    @error('school_division') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <!--<div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Academic Scope</label>
                    <input type="text" wire:model="school_level" readonly class="w-full px-3 py-2 text-xs font-bold rounded-lg border border-zinc-200 bg-zinc-100 dark:bg-zinc-800/80 text-zinc-500 cursor-not-allowed">
                </div>-->

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold rounded-lg shadow-sm transition flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-floppy-disk"></i>
                        <span>Save Profile & Logo Settings</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. ACADEMIC POLICY THRESHOLDS -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <i class="fas fa-sliders text-[#166534]"></i>
                <span>Academic Policy & Grading Thresholds</span>
            </h3>

            <form wire:submit.prevent="saveAcademicPolicies" class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Passing Grade Rating *</label>
                        <input type="number" step="0.01" max="99" min="60" wire:model.defer="passing_grade" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Honor Minimum Grade Cap *</label>
                        <input type="number" step="0.01" max="99" min="75" wire:model.defer="honor_grade_cap" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1 text-[10px]">Highest Honors Min</label>
                        <input type="number" step="0.01" max="99" min="90" wire:model.defer="highest_honor_min" required class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1 text-[10px]">High Honors Min</label>
                        <input type="number" step="0.01" max="99" min="85" wire:model.defer="high_honor_min" required class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1 text-[10px]">With Honors Min</label>
                        <input type="number" step="0.01" max="99" min="80" wire:model.defer="honors_min" required class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white font-bold rounded-lg shadow-sm transition">
                        Save Policy Thresholds
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. SYSTEM MAINTENANCE & BACKUP -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <i class="fas fa-database text-blue-600"></i>
                <span>Database Maintenance & Backups</span>
            </h3>

            <div class="space-y-3">
                <p class="text-zinc-500">Generate a full SQL database export containing all school year section placements, grades, and audit trail feeds.</p>
                
                <div class="p-3 bg-blue-50 dark:bg-blue-950/40 rounded-xl border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 flex items-center justify-between">
                    <div>
                        <span class="font-bold block">Instant Database Dump</span>
                        <span class="text-[11px] text-zinc-500">Includes all database tables and schema structure.</span>
                    </div>

                    <button wire:click="triggerDatabaseBackup" class="px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-lg shadow-sm transition text-xs cursor-pointer">
                        Download Dump
                    </button>
                </div>
            </div>
        </div>

        <!-- 4. ADMIN SECURITY & PASSWORD -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                <i class="fas fa-shield-halved text-rose-600"></i>
                <span>Administrator Security Credentials</span>
            </h3>

            <form wire:submit.prevent="updateAdminPassword" class="space-y-4">
                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Current Password *</label>
                    <input type="password" wire:model.defer="current_password" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    @error('current_password') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">New Password *</label>
                        <input type="password" wire:model.defer="new_password" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        @error('new_password') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Confirm New Password *</label>
                        <input type="password" wire:model.defer="new_password_confirmation" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-4 py-2 bg-zinc-900 hover:bg-black text-white font-bold rounded-lg shadow-sm transition cursor-pointer">
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
