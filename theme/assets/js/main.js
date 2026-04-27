/* Bhakti Bhawna — vanilla JS. Mobile nav + hero carousel. */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        initNav();
        initHeroCarousel();
    });

    /* ---------- Mobile nav ---------- */
    function initNav() {
        var toggle   = document.querySelector('.bb-nav-toggle');
        var closeBtn = document.querySelector('.bb-nav__close');
        var nav      = document.querySelector('.bb-nav');
        var backdrop = document.querySelector('.bb-nav-backdrop');
        if (!toggle || !nav) return;

        function open() {
            nav.classList.add('is-open');
            if (backdrop) backdrop.classList.add('is-open');
            document.body.classList.add('bb-no-scroll');
            toggle.setAttribute('aria-expanded', 'true');
        }
        function close() {
            nav.classList.remove('is-open');
            if (backdrop) backdrop.classList.remove('is-open');
            document.body.classList.remove('bb-no-scroll');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function handleToggle(e) {
            e.preventDefault();
            e.stopPropagation();
            if (nav.classList.contains('is-open')) close(); else open();
        }
        toggle.addEventListener('click', handleToggle);

        if (closeBtn) closeBtn.addEventListener('click', close);
        if (backdrop) backdrop.addEventListener('click', close);

        // Close drawer when a menu link is tapped (better mobile UX)
        nav.addEventListener('click', function (e) {
            var link = e.target.closest('a');
            if (link && nav.classList.contains('is-open')) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && nav.classList.contains('is-open')) close();
        });
    }

    /* ---------- Hero carousel ---------- */
    function initHeroCarousel() {
        var slides = document.querySelectorAll('.bb-hero__slide');
        var dots   = document.querySelectorAll('.bb-hero__dot');
        if (slides.length <= 1) return;

        var idx = 0;
        var total = slides.length;
        var timer = null;
        var AUTO_MS = 5500;

        function show(i) {
            idx = (i + total) % total;
            slides.forEach(function (s, j) { s.classList.toggle('is-active', j === idx); });
            dots.forEach(function (d, j)   { d.classList.toggle('is-active', j === idx); });
        }
        function next() { show(idx + 1); }
        function start() { stop(); timer = setInterval(next, AUTO_MS); }
        function stop()  { if (timer) clearInterval(timer); timer = null; }

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { show(i); start(); });
        });

        // Pause on hover (desktop)
        var hero = document.querySelector('.bb-hero');
        if (hero) {
            hero.addEventListener('mouseenter', stop);
            hero.addEventListener('mouseleave', start);
        }

        // Pause when tab hidden
        document.addEventListener('visibilitychange', function () {
            document.hidden ? stop() : start();
        });

        start();
    }
})();
