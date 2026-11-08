<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - Don Mariano Marcos NHS' : 'Academic Analytics - Don Mariano Marcos National High School' }}
</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@fonts

<!-- Font Awesome 6 for Standardized Academic Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<!-- Google Font for Official DepEd Blackletter Header -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&display=swap" rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])
<!-- ApexCharts for Academic Performance Visualizations -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

@fluxAppearance

<script>
    function toggleSecretDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('flux.appearance', isDark ? 'dark' : 'light');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    }

    (function() {
        const savedTheme = localStorage.getItem('flux.appearance') || localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (savedTheme === 'light') {
            document.documentElement.classList.remove('dark');
        }
    })();

    function confirmLogout(e, form) {
        if (e) e.preventDefault();
        
        let modal = document.getElementById('global-logout-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'global-logout-modal';
            modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-sm';
            modal.innerHTML = `
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto text-xl">
                        <i class="fas fa-right-from-bracket"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Confirm Logout</h3>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Are you sure you want to log out?</p>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" id="logout-cancel-btn" class="flex-1 py-2.5 px-4 rounded-xl border border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            Cancel
                        </button>
                        <button type="button" id="logout-confirm-btn" class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm">
                            Log Out
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
        
        modal.style.display = 'flex';
        
        document.getElementById('logout-cancel-btn').onclick = function() {
            modal.style.display = 'none';
        };
        
        document.getElementById('logout-confirm-btn').onclick = function() {
            modal.style.display = 'none';
            form.submit();
        };
        
        return false;
    }
</script>
