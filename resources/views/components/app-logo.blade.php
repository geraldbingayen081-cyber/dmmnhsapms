@props([
    'sidebar' => false,
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <x-app-logo-icon class="size-8" />
    <div class="flex flex-col text-start">
        <span class="text-sm font-bold tracking-tight text-zinc-900 dark:text-zinc-100 leading-tight">
            DMMNHS
        </span>
        <span class="text-[11px] font-medium text-sky-600 dark:text-sky-400 leading-none">
            Don Mariano Marcos NHS
        </span>
    </div>
</div>
