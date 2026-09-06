<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="h-full bg-zinc-50 dark:bg-zinc-950 font-sans text-zinc-900 dark:text-zinc-100 antialiased">
        <div class="min-h-screen flex">
            <!-- Desktop Fixed Sidebar -->
            <div class="hidden lg:block shrink-0">
                <x-student.sidebar />
            </div>

            <!-- Mobile Off-Canvas Sidebar Drawer Overlay -->
            <div id="student-mobile-drawer" class="fixed inset-0 z-50 lg:hidden hidden">
                <div id="student-drawer-backdrop" class="fixed inset-0 bg-zinc-950/60 backdrop-blur-sm transition-opacity"></div>
                <div class="fixed inset-y-0 left-0 max-w-xs w-full bg-[#1F2937] shadow-2xl flex flex-col z-10">
                    <div class="p-4 bg-[#166534] text-white flex items-center justify-between">
                        <span class="font-bold text-xs">Don Mariano Marcos NHS</span>
                        <button id="student-drawer-close" class="p-1 text-white hover:text-emerald-200">
                            <i class="fas fa-times text-base"></i>
                        </button>
                    </div>
                    <x-student.sidebar />
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <x-student.header :title="$title ?? 'Student Portal'" :subtitle="$subtitle ?? 'Academic Performance Monitoring System'" />

                <main class="flex-1 p-6 md:p-8 space-y-6 max-w-7xl w-full mx-auto">
                    <!-- Session Flash Messages -->
                    @if (session()->has('message'))
                        <div class="p-4 rounded-xl bg-[#DCFCE7] text-[#166534] border border-emerald-300 text-xs font-bold flex items-center gap-2">
                            <i class="fas fa-circle-check text-base"></i>
                            <span>{{ session('message') }}</span>
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-xs font-bold flex items-center gap-2">
                            <i class="fas fa-triangle-exclamation text-base"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const toggleBtn = document.getElementById('student-mobile-toggle');
                const closeBtn = document.getElementById('student-drawer-close');
                const backdrop = document.getElementById('student-drawer-backdrop');
                const drawer = document.getElementById('student-mobile-drawer');

                if (toggleBtn && drawer) {
                    toggleBtn.addEventListener('click', () => drawer.classList.remove('hidden'));
                }
                if (closeBtn && drawer) {
                    closeBtn.addEventListener('click', () => drawer.classList.add('hidden'));
                }
                if (backdrop && drawer) {
                    backdrop.addEventListener('click', () => drawer.classList.add('hidden'));
                }
            });
        </script>

        @stack('scripts')
        @fluxScripts
    </body>
</html>
