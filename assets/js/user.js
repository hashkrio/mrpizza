/* =========================================================
   Mr. Pizza Tavira — Custom JS
   ========================================================= */
(function () {
    'use strict';

    /* ---------- Sticky header shadow on scroll ---------- */
    const header = document.getElementById('siteHeader');
    const scrollBtn = document.getElementById('scrollTopBtn');

    function onScroll() {
        const y = window.scrollY || window.pageYOffset;

        if (header) {
            header.classList.toggle('scrolled', y > 30);
        }
        if (scrollBtn) {
            scrollBtn.classList.toggle('show', y > 400);
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- Scroll-to-top ---------- */
    if (scrollBtn) {
        scrollBtn.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ---------- Reveal-on-scroll animations (replays every time) ---------- */
    const animated = document.querySelectorAll('[data-animate]');

    if ('IntersectionObserver' in window && animated.length) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.intersectionRatio >= 0.12) {
                    // Element is in view → animate in
                    entry.target.classList.add('in-view');
                } else if (entry.intersectionRatio === 0) {
                    // Element fully left the viewport → reset so it can replay
                    entry.target.classList.remove('in-view');
                }
            });
        }, {
            threshold: [0, 0.12],
            rootMargin: '0px 0px -60px 0px'
        });

        animated.forEach(function (el) { observer.observe(el); });
    } else {
        // Fallback: show everything
        animated.forEach(function (el) { el.classList.add('in-view'); });
    }

    /* ---------- Close mobile offcanvas on link click ---------- */
    const sideNav = document.getElementById('sideNav');

    if (sideNav) {
        const sideNavLinks = sideNav.querySelectorAll('.nav-link');
        sideNavLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                const offcanvas = bootstrap.Offcanvas.getInstance(sideNav)
                    || new bootstrap.Offcanvas(sideNav);
                offcanvas.hide();
            });
        });
    }

})();