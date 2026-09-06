<div class="space-y-6 font-sans">
    @if (!$advisedSection)
        <!-- Not an adviser banner -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-12 text-center shadow-sm space-y-3">
            <div class="size-16 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center text-2xl mx-auto">
                <i class="fas fa-shield-halved"></i>
            </div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                No Official Class Advisory Assignment
            </h3>
            <p class="text-xs text-zinc-500 max-w-md mx-auto">
                You are currently assigned solely as a Subject Teacher for School Year {{ $activeSy?->school_year }}. The Class Advisory module is reserved for faculty members designated as Official Class Advisers by the school administration.
            </p>
            <div class="pt-2">
                <a href="{{ route('teacher.classes') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-[#166534] hover:bg-emerald-800 text-white rounded-xl font-bold text-xs shadow-sm transition">
                    <i class="fas fa-layer-group"></i>
                    <span>View My Subject Classes</span>
                </a>
            </div>
        </div>
    @else
        <!-- Section Header Card -->
        <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-xl bg-amber-100 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xl font-bold">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">
                        {{ $advisedSection->yearLevel?->name }} • Section {{ $advisedSection->section?->name }}
                    </h2>
                    <p class="text-xs text-zinc-500">
                        Official Class Advisory • {{ $enrollments->count() }} Enrolled Students • SY {{ $activeSy?->school_year }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('teacher.reports') }}" class="px-4 py-2.5 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i>
                    <span>Generate SF9 Cards</span>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs & Quarter Filter -->
        <div class="bg-white dark:bg-zinc-900 p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-xs">
            <div class="flex items-center gap-1.5 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl overflow-x-auto">
                <button wire:click="$set('activeTab', 'roster')" class="px-4 py-2 font-bold rounded-lg transition whitespace-nowrap {{ $activeTab === 'roster' ? 'bg-white dark:bg-zinc-900 text-[#166534] dark:text-emerald-400 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900' }}">
                    <i class="fas fa-users mr-1.5"></i>
                    <span>Class Roster & Parents ({{ $enrollments->count() }})</span>
                </button>
                <button wire:click="$set('activeTab', 'grades')" class="px-4 py-2 font-bold rounded-lg transition whitespace-nowrap {{ $activeTab === 'grades' ? 'bg-white dark:bg-zinc-900 text-[#166534] dark:text-emerald-400 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900' }}">
                    <i class="fas fa-table-cells mr-1.5"></i>
                    <span>Consolidated Grade Matrix</span>
                </button>
                <button wire:click="$set('activeTab', 'honors')" class="px-4 py-2 font-bold rounded-lg transition whitespace-nowrap {{ $activeTab === 'honors' ? 'bg-white dark:bg-zinc-900 text-[#166534] dark:text-emerald-400 shadow-xs' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900' }}">
                    <i class="fas fa-award mr-1.5 text-amber-500"></i>
                    <span>Achievers ({{ count($candidates) }})</span>
                </button>
            </div>

            @if ($activeTab === 'grades')
                <div class="flex items-center gap-2">
                    <span class="text-zinc-500 font-bold text-[11px]">Quarter:</span>
                    <select wire:model.live="selectedQuarterId" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        <option value="all">Consolidated / All Quarters</option>
                        @foreach ($quarters as $q)
                            <option value="{{ $q->id }}">{{ $q->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- TAB 1: Class Roster & Linked Parents -->
        @if ($activeTab === 'roster')
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-start">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3 px-4 text-center w-12">#</th>
                                <th class="py-3 px-4 text-start">Student ID / LRN</th>
                                <th class="py-3 px-4 text-start">Learner Name</th>
                                <th class="py-3 px-4 text-center">Gender</th>
                                <th class="py-3 px-4 text-start">Contact Number</th>
                                <th class="py-3 px-4 text-start">Linked Parent(s)</th>
                                <th class="py-3 px-4 text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                            @forelse ($enrollments as $idx => $enr)
                                @php
                                    $st = $enr->student;
                                    $parents = $st?->parents ?? collect();
                                @endphp
                                <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                                    <td class="py-3.5 px-4 text-center text-zinc-400 font-bold">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-mono font-bold text-[#166534] dark:text-emerald-400 block">{{ $st?->student_number }}</span>
                                        <span class="text-[10px] text-zinc-400 font-mono">{{ $st?->lrn ?? 'No LRN' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $st?->last_name }}, {{ $st?->first_name }} {{ $st?->middle_name }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center uppercase text-[11px] text-zinc-500 font-bold">
                                        {{ $st?->gender ?? '—' }}
                                    </td>
                                    <td class="py-3.5 px-4 text-zinc-600 dark:text-zinc-400 font-mono">
                                        {{ $st?->contact_number ?? 'Not set' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @forelse ($parents as $p)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800">
                                                    <i class="fas fa-user-tag text-[9px]"></i>
                                                    <span>{{ $p->full_name }} ({{ $p->pivot?->relationship ?? 'Parent' }})</span>
                                                    <button wire:click="unlinkParent({{ $p->id }}, {{ $st->id }})" wire:confirm="Are you sure you want to unlink this parent from the student?" title="Unlink" class="text-purple-400 hover:text-rose-600 ml-1">
                                                        <i class="fas fa-times text-[9px]"></i>
                                                    </button>
                                                </span>
                                            @empty
                                                <span class="text-zinc-400 text-[11px] italic">None linked</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-end">
                                        <button wire:click="openLinkParentModal({{ $st->id }})" title="Link Parent Account" class="px-2.5 py-1 text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-950/60 border border-purple-200 dark:border-purple-800 rounded-lg hover:bg-purple-100 transition inline-flex items-center gap-1">
                                            <i class="fas fa-link text-[10px]"></i>
                                            <span>Link Parent</span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-zinc-400 italic">
                                        No students officially enrolled in your advisory section.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 2: Consolidated Grade Matrix across subjects -->
        @if ($activeTab === 'grades')
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-start">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/80 text-zinc-500 uppercase text-[10px] font-bold tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                            <tr>
                                <th class="py-3 px-3 text-start w-48">Learner Name</th>
                                @foreach ($subjects as $sub)
                                    <th class="py-3 px-2 text-center" title="{{ $sub->subject_name }}">{{ $sub->subject_code }}</th>
                                @endforeach
                                <th class="py-3 px-3 text-center bg-emerald-50 dark:bg-emerald-950/50 text-[#166534] dark:text-emerald-300 font-extrabold">General Avg</th>
                                <th class="py-3 px-3 text-center">DepEd Standing</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                            @forelse ($enrollments as $enr)
                                @php
                                    $st = $enr->student;
                                    $grades = $enr->grades;
                                    $genAvg = $grades->count() > 0 ? $grades->avg('grade') : null;
                                    $minG = $grades->count() > 0 ? $grades->min('grade') : null;

                                    $honor = null;
                                    if ($genAvg >= 98.00 && $minG >= 85.00) $honor = 'With Highest Honors';
                                    elseif ($genAvg >= 95.00 && $minG >= 85.00) $honor = 'With High Honors';
                                    elseif ($genAvg >= 90.00 && $minG >= 85.00) $honor = 'With Honors';
                                @endphp
                                <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40 transition">
                                    <td class="py-3 px-3 font-bold text-zinc-900 dark:text-zinc-100">
                                        {{ $st?->last_name }}, {{ $st?->first_name }}
                                        <span class="block text-[10px] font-mono text-zinc-400 font-normal">{{ $st?->student_number }}</span>
                                    </td>
                                    @foreach ($subjects as $sub)
                                        @php
                                            $sg = $grades->where('sectionSubject.subject_id', $sub->id)->first();
                                            $gVal = $sg ? $sg->grade : null;
                                        @endphp
                                        <td class="py-3 px-2 text-center font-mono font-bold {{ $gVal !== null ? ($gVal >= 75 ? 'text-zinc-800 dark:text-zinc-200' : 'text-rose-600') : 'text-zinc-300' }}">
                                            {{ $gVal ? number_format($gVal, 1) : '—' }}
                                        </td>
                                    @endforeach
                                    <td class="py-3 px-3 text-center font-mono font-black text-sm bg-emerald-50/50 dark:bg-emerald-950/20 {{ $genAvg !== null ? ($genAvg >= 75 ? 'text-[#166534] dark:text-emerald-400' : 'text-rose-600') : 'text-zinc-400' }}">
                                        {{ $genAvg ? number_format($genAvg, 2) : '—' }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        @if ($honor)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                🌟 {{ $honor }}
                                            </span>
                                        @elseif ($genAvg !== null)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $genAvg >= 75 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                                {{ $genAvg >= 75 ? 'PASSED' : 'AT RISK' }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-zinc-400">Incomplete</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($subjects) + 3 }}" class="p-8 text-center text-zinc-400 italic">
                                        No grades recorded for this quarter period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- TAB 3: Academic Achievers & Subject Excellence Awards -->
        @if ($activeTab === 'honors')
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-sm p-6 space-y-6">
                <!-- Toolbar & Filter Header -->
                <div class="border-b border-zinc-100 dark:border-zinc-800 pb-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-medal text-amber-500"></i>
                            <span>Official Academic Achievers & Subject Excellence Awards</span>
                            <span class="text-xs text-zinc-400 font-normal">(SY {{ $activeSy?->school_year }})</span>
                        </h3>
                        <p class="text-[11px] text-zinc-500 mt-0.5">
                            DepEd Academic Honors (Highest, High, With Honors) and Subject Excellence Awards (Best in Mathematics, Science, English, etc.).
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Category Filter -->
                        <div>
                            <select wire:model.live="filterAchieverCategory" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                                <option value="all">All Achievers & Awards</option>
                                <option value="honors">General Honors Only</option>
                                <option value="highest">With Highest Honors (98-100)</option>
                                <option value="high">With High Honors (95-97)</option>
                                <option value="with_honors">With Honors (90-94)</option>
                                <option value="best_subjects">All Best in Subject Awards</option>
                                @foreach ($subjects as $sub)
                                    <option value="best_{{ $sub->subject_name }}">Best in {{ $sub->subject_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quarter Filter -->
                        <div>
                            <select wire:model.live="selectedQuarterId" class="px-3 py-1.5 text-xs font-bold rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-[#166534]">
                                <option value="all">Consolidated / All Quarters</option>
                                @foreach ($quarters as $q)
                                    <option value="{{ $q->id }}">{{ $q->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <span class="text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1 rounded-full whitespace-nowrap">
                            {{ count($candidates) }} {{ count($candidates) === 1 ? 'Achiever' : 'Achievers' }}
                        </span>
                    </div>
                </div>

                <!-- Achievers Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse ($candidates as $cand)
                        <div class="p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:border-amber-400 dark:hover:border-amber-600 transition space-y-4 flex flex-col justify-between shadow-xs">
                            <div class="space-y-3">
                                <!-- Student Header -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <div class="size-10 rounded-xl bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-black text-sm flex items-center justify-center border border-amber-200 dark:border-amber-800">
                                            {{ substr($cand['student']->first_name, 0, 1) }}{{ substr($cand['student']->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">
                                                {{ $cand['student']->full_name }}
                                            </h4>
                                            <p class="text-[11px] text-zinc-500 font-mono">{{ $cand['student']->student_number }}</p>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <span class="text-[10px] uppercase font-bold text-zinc-400 block">General Avg</span>
                                        <span class="font-black text-sm text-emerald-700 dark:text-emerald-400 font-mono">
                                            {{ number_format($cand['average'], 2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Awards Badges List -->
                                <div class="space-y-1.5 pt-2 border-t border-zinc-200/60 dark:border-zinc-700/60">
                                    <span class="text-[10px] uppercase font-bold text-zinc-400 block tracking-wider">
                                        Awards & Recognitions
                                    </span>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($cand['awards'] as $aw)
                                            @if ($aw['type'] === 'honor')
                                                @if ($aw['title'] === 'With Highest Honors')
                                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-[#DCFCE7] text-[#166534] border border-emerald-300 inline-flex items-center gap-1 shadow-xs">
                                                        <i class="fas fa-crown text-amber-500"></i>
                                                        <span>{{ $aw['title'] }}</span>
                                                    </span>
                                                @elseif ($aw['title'] === 'With High Honors')
                                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200 inline-flex items-center gap-1 shadow-xs">
                                                        <i class="fas fa-star text-blue-500"></i>
                                                        <span>{{ $aw['title'] }}</span>
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 inline-flex items-center gap-1 shadow-xs">
                                                        <i class="fas fa-award text-amber-600"></i>
                                                        <span>{{ $aw['title'] }}</span>
                                                    </span>
                                                @endif
                                            @elseif ($aw['type'] === 'subject')
                                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 inline-flex items-center gap-1 shadow-xs">
                                                    <i class="fas fa-medal text-indigo-500"></i>
                                                    <span>{{ $aw['title'] }}</span>
                                                    <span class="font-mono text-[10px] opacity-80">({{ number_format($aw['grade'], 1) }})</span>
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-bold border border-zinc-200 dark:border-zinc-700">
                                                    {{ $aw['title'] }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Action / Registry Bar -->
                            <div class="pt-3 border-t border-zinc-200/60 dark:border-zinc-700/60 flex items-center justify-between text-xs">
                                <span class="text-[11px] text-zinc-400 font-medium">
                                    Lowest grade: <strong class="text-zinc-700 dark:text-zinc-300 font-mono">{{ number_format($cand['lowest'], 1) }}</strong>
                                </span>

                                <div class="flex items-center gap-1.5">
                                    @php
                                        $pendingAwards = collect($cand['awards'])->where('awarded', false);
                                    @endphp
                                    @if ($pendingAwards->isNotEmpty())
                                        @foreach ($pendingAwards->take(1) as $paw)
                                            <button wire:click="awardHonorTitle({{ $cand['enrollment_id'] }}, '{{ $paw['title'] }}')" class="px-2.5 py-1 bg-[#166534] hover:bg-emerald-800 text-white font-bold text-[10px] rounded-lg shadow-xs transition flex items-center gap-1 cursor-pointer" title="Officially award this title into student record">
                                                <i class="fas fa-certificate text-[9px]"></i>
                                                <span>Award {{ Str::limit($paw['title'], 18) }}</span>
                                            </button>
                                        @endforeach
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 flex items-center gap-1">
                                            <i class="fas fa-check-circle"></i>
                                            <span>Awarded in System</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-3 p-12 text-center text-zinc-400 italic bg-zinc-50 dark:bg-zinc-800/20 rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-700">
                            <i class="fas fa-award text-3xl mb-2 text-zinc-300 block"></i>
                            No students currently meet the selected achiever criteria for this period.
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- LINK PARENT MODAL -->
        @if ($showLinkParentModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-zinc-950/50 backdrop-blur-sm" wire:click="$set('showLinkParentModal', false)"></div>
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xl w-full max-w-md p-6 relative z-10 space-y-4 text-xs">
                    <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                            <i class="fas fa-link text-[#166534]"></i>
                            <span>Link Parent Account to Advisee</span>
                        </h3>
                        <button wire:click="$set('showLinkParentModal', false)" class="text-zinc-400 hover:text-zinc-600">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>

                    @if ($targetStudent)
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800/60 rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase block">Selected Advisee:</span>
                            <span class="font-bold text-zinc-900 dark:text-zinc-100 text-sm">{{ $targetStudent->full_name }}</span>
                            <span class="text-[11px] text-zinc-500 font-mono block">{{ $targetStudent->student_number }}</span>
                        </div>
                    @endif

                    <form wire:submit.prevent="linkParentToAdvisee" class="space-y-4">
                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Search Parent Name or ID</label>
                            <input type="text" wire:model.live.debounce.300ms="parentSearch" placeholder="Type name or parent ID..." class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 mb-2">

                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Select Parent Account *</label>
                            <select wire:model.defer="selectedParentId" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                <option value="">-- Choose Parent Account --</option>
                                @foreach ($availableParents as $p)
                                    <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->user?->login_id ?? 'No ID' }})</option>
                                @endforeach
                            </select>
                            @error('selectedParentId') <span class="text-rose-600 text-[10px] font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Relationship to Student *</label>
                            <select wire:model.defer="relationship" required class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Guardian">Guardian</option>
                                <option value="Grandmother">Grandmother</option>
                                <option value="Grandfather">Grandfather</option>
                                <option value="Aunt">Aunt</option>
                                <option value="Uncle">Uncle</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <a href="{{ route('teacher.parents') }}" class="text-[11px] font-bold text-[#166534] dark:text-emerald-400 hover:underline">
                                + Create New Parent Account
                            </a>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('showLinkParentModal', false)" class="px-4 py-2 text-xs font-bold rounded-lg border border-zinc-300 text-zinc-700 hover:bg-zinc-100">Cancel</button>
                                <button type="submit" class="px-4 py-2 text-xs font-bold rounded-lg bg-[#166534] text-white hover:bg-emerald-800 shadow-sm">Link Parent</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
