<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-b from-emerald-950/15 via-zinc-50 to-zinc-100 dark:from-zinc-950 dark:via-zinc-950 dark:to-zinc-900 font-sans text-zinc-900 dark:text-zinc-100 antialiased selection:bg-[#166534] selection:text-white">
        <!-- Ambient Radial Background Blur -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-emerald-500/10 dark:bg-emerald-600/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-amber-500/10 dark:bg-amber-600/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 min-h-screen flex flex-col items-center justify-between p-4 sm:p-8">
            <div class="w-full max-w-md my-auto space-y-6">
                {{ $slot }}
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
