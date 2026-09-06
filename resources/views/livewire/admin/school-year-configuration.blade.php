<div class="space-y-6 font-sans">
    <!-- Header & Action Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.school-years') }}" class="p-2 text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 rounded-lg transition">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-sliders text-[#166534]"></i>
                    <span>SY {{ $schoolYear->school_year }} Configuration</span>
                </h2>
                <p class="text-xs text-zinc-500 mt-0.5">Academic quarters, grade level section instances, and faculty advisers.</p>
            </div>
        </div>

        <button wire:click="openAddSectionModal" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2 self-start sm:self-auto cursor-pointer">
            <i class="fas fa-plus"></i>
            <span>Add Section Instance</span>
        </button>
    </div>

    <!-- 1. GENERAL INFORMATION SUMMARY CARD -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-circle-info text-[#166534]"></i>
                <span>General School Year Information</span>
            </h3>
            @if ($schoolYear->status === 'active')
                <span class="px-3 py-1 rounded-md text-[10px] font-bold bg-[#DCFCE7] text-[#166534] border border-emerald-200 uppercase">
                    Current Active School Year
                </span>
            @else
                <span class="px-3 py-1 rounded-md text-[10px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200 uppercase">
                    Status: {{ $schoolYear->status }}
                </span>
            @endif
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">School Year</span>
                <span class="font-mono font-bold text-base text-[#166534]">SY {{ $schoolYear->school_year }}</span>
            </div>

            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">Start Date</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ \Carbon\Carbon::parse($schoolYear->start_date)->format('F d, Y') }}</span>
            </div>

            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">End Date</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100">{{ \Carbon\Carbon::parse($schoolYear->end_date)->format('F d, Y') }}</span>
            </div>

            <div class="p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <span class="text-zinc-400 text-[10px] font-bold uppercase tracking-wider block">System Status</span>
                <span class="font-bold text-zinc-900 dark:text-zinc-100 capitalize">{{ $schoolYear->status }}</span>
            </div>
        </div>
    </div>

    <!-- 2. QUARTERS OVERVIEW -->
    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-4">
        <div class="border-b border-zinc-100 dark:border-zinc-800 pb-3 flex items-center justify-between">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-list-ol text-[#166534]"></i>
                    <span>Academic Quarters</span>
                </h3>
                <p class="text-[11px] text-zinc-500">Configured evaluation quarters for SY {{ $schoolYear->school_year }}</p>
            </div>
            <a href="{{ route('admin.school-years.quarters', $schoolYear->id) }}" class="text-xs font-bold text-[#166534] hover:underline">
                Manage Quarters →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            @forelse ($quarters as $q)
                <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-2 {{ $q->status === 'active' ? 'bg-[#DCFCE7]/30 border-emerald-300' : 'bg-zinc-50 dark:bg-zinc-800/40' }}">
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-bold text-xs text-[#166534]">{{ $q->short_name }}</span>
                        @if ($q->status === 'active')
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DCFCE7] text-[#166534] border border-emerald-200">Active</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-zinc-100 text-zinc-600 border border-zinc-200 capitalize">{{ $q->status }}</span>
                        @endif
                    </div>
                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block">{{ $q->name }}</span>
                    <span class="text-[10px] text-zinc-500 block">
                        {{ $q->start_date && $q->end_date ? \Carbon\Carbon::parse($q->start_date)->format('M d') . ' - ' . \Carbon\Carbon::parse($q->end_date)->format('M d') : 'Dates Not Set' }}
                    </span>
                </div>
            @empty
                <div class="col-span-4 p-4 text-center text-zinc-400 italic">No quarters configured.</div>
            @endforelse
        </div>
    </div>

    <!-- 3. SECTIONS GROUPED BY YEAR LEVEL -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <i class="fas fa-users-rectangle text-[#166534]"></i>
                <span>School Year Sections Grouped by Year Level</span>
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($sectionsByGradeLevel as $ylId => $data)
                @php
                    $yl = $data['yearLevel'];
                    $gradeSections = $data['sections'];
                @endphp
                <div class="bg-white dark:bg-zinc-900 p-5 rounded-xl border border-zinc-200 dark:border-zinc-800 shadow-sm space-y-3">
                    <div class="border-b border-zinc-100 dark:border-zinc-800 pb-2.5 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded font-mono font-bold text-xs bg-[#DCFCE7] text-[#166534] border border-emerald-200">
                                {{ $yl->code }}
                            </span>
                            <h4 class="font-bold text-xs text-zinc-900 dark:text-zinc-100">{{ $yl->name }}</h4>
                        </div>
                        <span class="text-[10px] font-bold text-zinc-400">
                            {{ count($gradeSections) }} {{ Str::plural('Section', count($gradeSections)) }}
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        @forelse ($gradeSections as $sys)
                            <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 flex items-center justify-between gap-3">
                                <div>
                                    <span class="font-bold text-zinc-900 dark:text-zinc-100 block text-xs">
                                        Section {{ $sys->section?->name }}
                                    </span>
                                    <span class="text-[11px] text-zinc-500 block">
                                        <i class="fas fa-user-shield text-[#166534] text-[10px] mr-1"></i>
                                        Adviser: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $sys->adviser?->full_name ?? 'Unassigned' }}</span>
                                    </span>
                                    <span class="text-[10px] text-zinc-400 block mt-0.5">
                                        Assigned Subject Loads: {{ $sys->sectionSubjects->count() }} Subjects
                                    </span>
                                </div>

                                <button wire:click="removeSectionInstance({{ $sys->id }})" title="Remove Section Instance" class="p-1.5 text-zinc-400 hover:text-rose-600 hover:bg-rose-50 rounded transition">
                                    <i class="fas fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        @empty
                            <div class="p-4 text-center text-zinc-400 italic text-[11px]">
                                No section instances configured for {{ $yl->name }}.
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ADD SECTION INSTANCE MODAL -->
    @if ($showAddSectionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showAddSectionModal', false)"></div>
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-5 text-xs">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                        <i class="fas fa-plus text-[#166534]"></i>
                        <span>Add Section Instance to SY {{ $schoolYear->school_year }}</span>
                    </h3>
                    <button wire:click="$set('showAddSectionModal', false)" class="text-zinc-400 hover:text-zinc-600">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>

                <form wire:submit.prevent="addSectionInstance" class="space-y-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Year Level *</label>
                        <select wire:model.defer="year_level_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Year Level...</option>
                            @foreach ($yearLevels as $yl)
                                <option value="{{ $yl->id }}">{{ $yl->name }} ({{ $yl->code }})</option>
                            @endforeach
                        </select>
                        @error('year_level_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Section Master Name *</label>
                        <select wire:model.defer="section_id" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">Select Section Name...</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                            @endforeach
                        </select>
                        @error('section_id') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Class Adviser (Optional)</label>
                        <select wire:model.defer="adviser_id" class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                            <option value="">No Adviser Assigned Yet</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->employee_number }} - {{ $t->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="button" wire:click="$set('showAddSectionModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Add Section Instance</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
