<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-zinc-200 dark:border-zinc-700 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                <span>👥 User Account & Parent Linkage Management</span>
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                Create user accounts with auto-formatted IDs (ADM-0001, EMP-0001, 2026-0001, PARENT-0001) and link parent accounts to student records.
            </p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
            ✅ {{ session('message') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Account Creation Form -->
        <div class="bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">➕ Create New Account</h2>
            <form wire:submit.prevent="createUser" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">User Role</label>
                    <select wire:model.live="role" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-semibold">
                        <option value="student">🎓 Student (ID: 2026-XXXX)</option>
                        <option value="teacher">👨‍🏫 Teacher / Employee (ID: EMP-XXXX)</option>
                        <option value="parent">👨‍👩‍👧 Parent / Guardian (ID: PARENT-XXXX)</option>
                        <option value="administrator">🛡️ Administrator (ID: ADM-XXXX)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Full Name</label>
                    <input type="text" wire:model="name" placeholder="e.g. Juan Dela Cruz" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                    @error('name') <span class="text-rose-500">{{ $message }}</span> @error
                </div>

                <div>
                    <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email Address (Optional)</label>
                    <input type="email" wire:model="email" placeholder="Auto-generated if empty" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                </div>

                @if ($role === 'student')
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">LRN (Learner Reference Number)</label>
                        <input type="text" wire:model="lrn" placeholder="12-digit LRN" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Section Assignment</label>
                        <select wire:model="section_id" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}">{{ $sec->name }} ({{ $sec->gradeLevel->name }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-xl transition shadow-sm">
                        Create Account & Generate ID
                    </button>
                </div>
            </form>

            <!-- Parent Linker Card -->
            <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700 space-y-4">
                <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                    <span>🔗 Link Parent to Child</span>
                </h3>
                <form wire:submit.prevent="linkParentStudent" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Select Parent Account</label>
                        <select wire:model="selectedParentId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Parent --</option>
                            @foreach ($parents as $p)
                                <option value="{{ $p->id }}">{{ $p->custom_parent_id }} - {{ $p->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Select Student (Child)</label>
                        <select wire:model="selectedStudentId" class="w-full px-3 py-2 rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
                            <option value="">-- Choose Student --</option>
                            @foreach ($students as $st)
                                <option value="{{ $st->id }}">{{ $st->custom_student_id }} - {{ $st->user->name }} ({{ $st->section?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl transition">
                        Link Parent & Student
                    </button>
                </form>
            </div>
        </div>

        <!-- Users Directory Table -->
        <div class="lg:col-span-2 bg-white dark:bg-zinc-800 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">📋 User Directory</h2>
                <div class="flex items-center gap-2">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search ID or Name..." class="px-3 py-1.5 text-xs rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900">
                    <select wire:model.live="filterRole" class="px-3 py-1.5 text-xs rounded-xl border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-900 font-semibold">
                        <option value="all">All Roles</option>
                        <option value="administrator">Admin</option>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700 text-zinc-500 uppercase tracking-wider">
                            <th class="py-2.5 px-3 text-start">User ID</th>
                            <th class="py-2.5 px-3 text-start">Name</th>
                            <th class="py-2.5 px-3 text-start">Role</th>
                            <th class="py-2.5 px-3 text-start">Default Password</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 font-medium">
                        @foreach ($users as $u)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                                <td class="py-3 px-3 font-mono font-bold text-sky-600 dark:text-sky-400">
                                    {{ $u->user_id }}
                                </td>
                                <td class="py-3 px-3 font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $u->name }}
                                </td>
                                <td class="py-3 px-3">
                                    @if ($u->role === 'administrator')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Admin</span>
                                    @elseif ($u->role === 'teacher')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Teacher</span>
                                    @elseif ($u->role === 'student')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Student</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Parent</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono text-zinc-500 text-[11px]">
                                    {{ $u->role === 'administrator' ? 'admin123' : 'password' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
