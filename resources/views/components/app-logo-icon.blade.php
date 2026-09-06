@php
    $customLogoUrl = \App\Models\SystemSetting::logoUrl();
@endphp

@if ($customLogoUrl)
    <div {{ $attributes->merge(['class' => 'relative flex items-center justify-center rounded-xl bg-white p-1 shadow-md border border-zinc-200 dark:border-zinc-700 overflow-hidden']) }}>
        <img src="{{ $customLogoUrl }}" alt="Logo" class="max-w-full max-h-full w-auto h-auto object-contain block" style="max-width: 100%; max-height: 100%; object-fit: contain;">
    </div>
@else
    <div {{ $attributes->merge(['class' => 'relative flex items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 via-emerald-500 to-amber-400 p-2 shadow-md']) }}>
        <!-- DMMNHS Academic Crest SVG -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6 text-white drop-shadow-sm">
            <path d="M11.7 2.805a.75.75 0 0 1 .6 0l9.45 4.2a.75.75 0 0 1 0 1.37l-2.02.897v5.928a.75.75 0 0 1-.22.53l-3.5 3.5a.75.75 0 0 1-1.06 0l-3.5-3.5a.75.75 0 0 1-.22-.53V9.272L9.75 8.615v6.51a2.25 2.25 0 0 1-2.25 2.25H6a.75.75 0 0 1 0-1.5h1.5a.75.75 0 0 0 .75-.75V7.948L2.25 5.8a.75.75 0 0 1 0-1.37l9.45-4.2ZM12 4.135 4.43 7.5 12 10.865 19.57 7.5 12 4.135Z" />
        </svg>
    </div>
@endif
