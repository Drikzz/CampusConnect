<div id="top-loading-bar"
    class="fixed top-0 left-0 w-full h-0.5 bg-white transform -translate-x-full transition-transform duration-500 z-[9999]">
</div>

<style>
    #top-loading-bar {
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }

    @keyframes loading {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(0);
        }
    }

    .loading-start {
        animation: loading 1s ease-out forwards;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadingBar = document.getElementById('top-loading-bar');

        // Show loading bar on navigation
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && !link.hasAttribute('data-no-loader')) {
                loadingBar.style.transform = 'translateX(-100%)';
                loadingBar.style.opacity = '1';
                requestAnimationFrame(() => {
                    loadingBar.classList.add('loading-start');
                });
            }
        });

        // Handle form submissions
        document.addEventListener('submit', function() {
            loadingBar.style.transform = 'translateX(-100%)';
            loadingBar.style.opacity = '1';
            requestAnimationFrame(() => {
                loadingBar.classList.add('loading-start');
            });
        });
    });
</script>
