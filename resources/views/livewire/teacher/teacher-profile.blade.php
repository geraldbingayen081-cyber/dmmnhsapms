<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <!-- Top Banner Card -->
    <div class="bg-gradient-to-r from-[#166534] to-emerald-800 rounded-3xl p-6 text-white shadow-md relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="shrink-0" style="width: 64px; height: 64px;">
                    @if (auth()->user()->profile_photo_path)
                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-white/40 shadow-md shrink-0 block" style="width: 64px; height: 64px; min-width: 64px; max-width: 64px; min-height: 64px; max-height: 64px; object-fit: cover;">
                    @else
                        <div class="w-16 h-16 rounded-full bg-amber-400 text-emerald-950 font-black text-xl flex items-center justify-center border-2 border-white/40 shadow-md shrink-0" style="width: 64px; height: 64px; min-width: 64px; max-width: 64px; min-height: 64px; max-height: 64px;">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-black tracking-tight">{{ auth()->user()->name }}</h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-400 text-emerald-950 uppercase tracking-wider">Faculty</span>
                    </div>
                    <p class="text-xs text-emerald-100 font-mono mt-1">ID: {{ auth()->user()->login_id }} • {{ auth()->user()->email ?? 'No email registered' }}</p>
                    <p class="text-xs text-emerald-200 mt-0.5">Faculty Member • Don Mariano Marcos National High School</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 1. Profile Photo Upload Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-5">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-camera text-emerald-600 dark:text-emerald-400"></i>
                    <span>Profile Picture</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Upload your official avatar image (JPEG, PNG, WebP up to 5MB max).</p>
            </div>

            @if (session()->has('photo_message'))
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('photo_message') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="updatePhoto" class="space-y-4">
                <div class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-2xl bg-zinc-50 dark:bg-zinc-900/50">
                    @if ($photo)
                        <div class="mb-3 text-center flex flex-col items-center">
                            <span class="text-[10px] font-bold text-amber-600 block mb-1">New Image Preview:</span>
                            <img src="{{ $photo->temporaryUrl() }}" class="w-20 h-20 rounded-full object-cover shadow border-2 border-amber-400 mx-auto shrink-0 block" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px; min-height: 80px; max-height: 80px; object-fit: cover;">
                        </div>
                    @elseif (auth()->user()->profile_photo_path)
                        <img src="{{ auth()->user()->profilePhotoUrl() }}" class="w-20 h-20 rounded-full object-cover shadow border-2 border-zinc-200 dark:border-zinc-700 mb-3 mx-auto shrink-0 block" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px; min-height: 80px; max-height: 80px; object-fit: cover;">
                    @else
                        <div class="w-20 h-20 rounded-full bg-emerald-100 dark:bg-emerald-950 text-[#166534] dark:text-emerald-300 font-extrabold text-2xl flex items-center justify-center mb-3 mx-auto border-2 border-emerald-600/30 shrink-0" style="width: 80px; height: 80px; min-width: 80px; max-width: 80px; min-height: 80px; max-height: 80px;">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif

                    <label for="teacher-photo-input" class="cursor-pointer px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-upload"></i>
                        <span>Select Photo (Max 5MB)</span>
                    </label>
                    <input type="file" id="teacher-photo-input" wire:model="photo" accept="image/*" class="hidden">
                    
                    <span class="text-[10px] text-zinc-400 mt-2 font-medium">Supported formats: JPG, PNG, WEBP • Max 5MB</span>
                </div>

                @error('photo') 
                    <span class="text-rose-600 text-xs font-bold block">{{ $message }}</span> 
                @enderror

                <div class="flex items-center gap-2 pt-2">
                    @if ($photo)
                        <button type="submit" wire:loading.attr="disabled" class="flex-1 py-2.5 px-4 bg-[#166534] hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-save" wire:loading.remove></i>
                            <i class="fas fa-spinner fa-spin" wire:loading></i>
                            <span>Save Photo</span>
                        </button>
                    @endif

                    @if (auth()->user()->profile_photo_path)
                        <button type="button" wire:click="deletePhoto" wire:confirm="Are you sure you want to remove your profile picture?" class="px-3 py-2.5 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 rounded-xl text-xs font-bold border border-rose-200 dark:border-rose-800 transition">
                            Remove
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <!-- 2. Personal Information Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-5 lg:col-span-2">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-id-card text-emerald-600 dark:text-emerald-400"></i>
                    <span>Personal Information</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Update your basic personal details and contact numbers.</p>
            </div>

            @if (session()->has('info_message'))
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-circle-check"></i>
                    <span>{{ session('info_message') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="updatePersonalInfo" class="space-y-4 text-xs">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">First Name *</label>
                        <input type="text" wire:model="first_name" required class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('first_name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Middle Name</label>
                        <input type="text" wire:model="middle_name" placeholder="Optional" class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('middle_name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Last Name *</label>
                        <input type="text" wire:model="last_name" required class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('last_name') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Suffix</label>
                        <input type="text" wire:model="suffix" placeholder="e.g. Jr., III (Optional)" class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('suffix') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Contact Mobile Number</label>
                        <input type="text" wire:model="contact_number" placeholder="e.g. 09123456789" class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('contact_number') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Email Address</label>
                        <input type="email" wire:model="email" placeholder="e.g. teacher@deped.gov.ph" class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('email') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-3">
                    <button type="submit" class="py-2.5 px-6 bg-[#166534] hover:bg-emerald-800 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        <span>Save Personal Information</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- 3. Security Credentials Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-2xl p-6 border border-zinc-200 dark:border-zinc-700 shadow-sm space-y-5 lg:col-span-3">
            <div>
                <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                    <i class="fas fa-lock text-amber-500"></i>
                    <span>Security Credentials & Password</span>
                </h2>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Ensure your account stays secure by using a strong password.</p>
            </div>

            @if (session()->has('security_message'))
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs font-semibold flex items-center gap-2">
                    <i class="fas fa-shield-check"></i>
                    <span>{{ session('security_message') }}</span>
                </div>
            @endif

            <form wire:submit.prevent="updatePassword" class="space-y-4 text-xs max-w-2xl">
                <div>
                    <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Current Password *</label>
                    <input type="password" wire:model="current_password" required class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                    @error('current_password') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">New Password (Min 8 Characters) *</label>
                        <input type="password" wire:model="new_password" required class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                        @error('new_password') <span class="text-rose-600 text-[11px] font-bold block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-zinc-700 dark:text-zinc-300 mb-1">Confirm New Password *</label>
                        <input type="password" wire:model="new_password_confirmation" required class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 font-medium">
                    </div>
                </div>

                <div class="flex justify-start pt-2">
                    <button type="submit" class="py-2.5 px-6 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2">
                        <i class="fas fa-[#166534] fa-key"></i>
                        <span>Update Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
