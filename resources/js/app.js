// BFCache Protection: Force reload if page is restored from Back-Forward Cache
window.addEventListener('pageshow', function (event) {
    if (event.persisted || (performance && performance.navigation && performance.navigation.type === 2)) {
        window.location.reload();
    }
});

// Handle Protected Route Browser Navigation & Auto-Logout on Back Button
document.addEventListener('DOMContentLoaded', function () {
    const pathname = window.location.pathname;
    const isProtectedRoute = /^\/(admin|teacher|student|parent|dashboard)(\/|$)/i.test(pathname);

    if (isProtectedRoute) {
        // Prevent storing state in browser history cache
        history.pushState({ protected: true }, '', window.location.href);

        window.addEventListener('popstate', function () {
            // Clear all local & session storage
            try {
                localStorage.clear();
                sessionStorage.clear();
            } catch (e) {
                console.warn('Storage clearance error:', e);
            }

            // Redirect to session termination / logout
            window.location.href = '/logout';
        });
    }

    if (pathname === '/login' || pathname === '/') {
        try {
            localStorage.clear();
            sessionStorage.clear();
        } catch (e) {}
    }
});
