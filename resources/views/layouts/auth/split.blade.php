<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 font-sans text-zinc-900 dark:text-zinc-100 antialiased">
        <div class="relative min-h-screen grid lg:grid-cols-12">
            <!-- Left Hero Panel (Desktop) -->
            <div class="relative hidden lg:flex lg:col-span-5 xl:col-span-6 bg-gradient-to-br from-emerald-950 via-[#064e3b] to-[#166534] p-12 text-white flex-col justify-between overflow-hidden shadow-2xl">
                <!-- Background Geometric Grid Pattern -->
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>
                
                <!-- Glowing Ambient Backdrop -->
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Top School Branding -->
                <div class="relative z-10 flex items-center gap-3">
                    <div class="size-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-400 shadow-inner">
                        <i class="fas fa-graduation-cap text-2xl"></i>
                    </div>
                    <div>
                        <span class="font-bold text-sm tracking-wide text-white block">Don Mariano Marcos NHS</span>
                        <span class="text-[11px] text-emerald-200 block font-medium">Dummun, Gattaran, Cagayan, Philippines</span>
                    </div>
                </div>

                <!-- Center Hero Section -->
                <div class="relative z-10 my-auto space-y-6 max-w-lg">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-amber-300">
                        <i class="fas fa-chart-line text-amber-400"></i>
                        <span>APMS Analytics Portal</span>
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Academic Performance Monitoring System <span class="text-amber-400">with Analytics</span>
                    </h1>

                    <p class="text-xs text-emerald-100/90 leading-relaxed font-normal">
                        Streamlined grade monitoring, automated DepEd honor roll recognition, section assignment engine, and comprehensive academic analytics for Junior High School.
                    </p>

                    <!-- Feature Badges -->
                    <div class="grid grid-cols-2 gap-3 text-xs pt-2 font-medium">
                        <div class="p-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 flex items-center gap-2.5">
                            <i class="fas fa-chart-pie text-amber-400 text-sm"></i>
                            <span>Real-Time Analytics</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 flex items-center gap-2.5">
                            <i class="fas fa-award text-amber-400 text-sm"></i>
                            <span>DepEd Honor Roll</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 flex items-center gap-2.5">
                            <i class="fas fa-users text-amber-400 text-sm"></i>
                            <span>Multi-Role Portals</span>
                        </div>
                        <div class="p-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/15 flex items-center gap-2.5">
                            <i class="fas fa-shield-halved text-amber-400 text-sm"></i>
                            <span>Secure Data Vault</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Copyright -->
                <div class="relative z-10 text-[11px] text-emerald-200/70 border-t border-white/10 pt-4 flex items-center justify-between">
                    <span>© {{ date('Y') }} DMMNHS Academic System</span>
                    <span class="font-mono">DepEd JHS Standard</span>
                </div>
            </div>

            <!-- Right Form Panel -->
            <div class="lg:col-span-7 xl:col-span-6 flex items-center justify-center p-6 sm:p-12">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
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
