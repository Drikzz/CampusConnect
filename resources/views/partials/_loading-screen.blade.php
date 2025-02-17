{{-- Full-screen loader for first visit --}}
<div id="initial-loader"
    class="fixed inset-0 z-50 flex items-center justify-center bg-primary-color transition-opacity duration-500">
    <div class="text-center">
        <img src="{{ asset('imgs/CampusConnect.png') }}" alt="CampusConnect Logo" class="w-64 h-64 mx-auto animate-pulse">
        <div class="mt-4">
            <div class="w-32 h-1 bg-white/20 rounded-full mx-auto overflow-hidden">
                <div id="loading-bar" class="w-full h-full bg-white"></div>
            </div>
        </div>
    </div>
</div>

{{-- Simple center loader for subsequent navigations --}}
<div id="navigation-loader"
    class="fixed inset-0 z-50 flex items-center justify-center bg-white/50 opacity-0 pointer-events-none transition-opacity duration-300">
    <div class="p-4 rounded-lg">
        <img src="{{ asset('imgs/CampusConnect.png') }}" alt="Loading..." class="w-16 h-16 animate-pulse">
    </div>
</div>

<style>
    @keyframes loadingProgress {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(0);
        }
    }

    #loading-bar {
        animation: loadingProgress 2s ease-in-out forwards;
    }

    .hide-loading {
        opacity: 0 !important;
        transition: opacity 0.5s ease-out;
        pointer-events: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const initialLoader = document.getElementById('initial-loader');
        const navigationLoader = document.getElementById('navigation-loader');

        // Handle first visit
        if (!localStorage.getItem('firstVisitDone')) {
            initialLoader.style.display = 'flex';
            localStorage.setItem('firstVisitDone', 'true');

            window.addEventListener('load', function() {
                setTimeout(() => {
                    initialLoader.style.opacity = '0';
                    setTimeout(() => {
                        initialLoader.style.display = 'none';
                    }, 500);
                }, 2000);
            });
        } else {
            initialLoader.style.display = 'none';
        }

        // Handle logo clicks and navigation
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[data-nav-loader]');
            if (link) {
                e.preventDefault();
                navigationLoader.style.opacity = '1';
                navigationLoader.style.pointerEvents = 'auto';

                setTimeout(() => {
                    window.location = link.href;
                }, 100);
            }
        });
    });
</script>
