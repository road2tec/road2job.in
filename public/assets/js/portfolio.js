/**
 * Premium portfolio page behavior - vanilla JS, no build step, no
 * dependency (same convention as scroll-reveal.js/multi-select.js).
 * Three independent, IntersectionObserver-based pieces, each with a
 * static/no-animation fallback for prefers-reduced-motion or missing IO:
 *   1. Sticky sub-nav offset (sits under the global site-nav) + scroll-spy
 *   2. Typing effect cycling through data-roles on .portfolio-hero__typed
 *   3. Count-up animation on [data-counter]
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.addEventListener('DOMContentLoaded', function () {
        positionSubnav();
        window.addEventListener('resize', positionSubnav);
        initScrollSpy();
        initTypingEffect();
        initCounters();
    });

    function positionSubnav() {
        var subnav = document.querySelector('.portfolio-subnav');
        var siteNav = document.querySelector('.site-nav');
        if (!subnav || !siteNav) return;
        subnav.style.top = siteNav.offsetHeight + 'px';
    }

    function initScrollSpy() {
        var links = Array.prototype.slice.call(document.querySelectorAll('.portfolio-subnav__link'));
        if (links.length === 0) return;

        var sections = links
            .map(function (link) {
                var id = link.getAttribute('href').replace('#', '');
                return document.getElementById(id);
            })
            .filter(Boolean);

        if (sections.length === 0) return;

        if (!('IntersectionObserver' in window)) return;

        var subnav = document.querySelector('.portfolio-subnav');
        var offset = subnav ? subnav.offsetHeight + 20 : 80;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var id = entry.target.getAttribute('id');
                links.forEach(function (link) {
                    link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
                });
            });
        }, { rootMargin: '-' + offset + 'px 0px -70% 0px', threshold: 0 });

        sections.forEach(function (section) { observer.observe(section); });
    }

    function initTypingEffect() {
        var el = document.querySelector('.portfolio-hero__typed');
        if (!el) return;

        var roles = [];
        try {
            roles = JSON.parse(el.getAttribute('data-roles') || '[]');
        } catch (e) {
            roles = [];
        }
        roles = roles.filter(function (r) { return String(r || '').trim() !== ''; });
        if (roles.length === 0) return;

        var textEl = document.createElement('span');
        textEl.className = 'text';
        var cursorEl = document.createElement('span');
        cursorEl.className = 'cursor';
        el.innerHTML = '';
        el.appendChild(textEl);
        el.appendChild(cursorEl);

        if (reduceMotion) {
            textEl.textContent = roles[0];
            return;
        }

        var roleIndex = 0;
        var charIndex = 0;
        var deleting = false;

        function tick() {
            var current = roles[roleIndex];

            if (!deleting) {
                charIndex++;
                textEl.textContent = current.slice(0, charIndex);
                if (charIndex === current.length) {
                    deleting = true;
                    setTimeout(tick, 1400);
                    return;
                }
                setTimeout(tick, 65);
                return;
            }

            charIndex--;
            textEl.textContent = current.slice(0, charIndex);
            if (charIndex === 0) {
                deleting = false;
                roleIndex = (roleIndex + 1) % roles.length;
                setTimeout(tick, 300);
                return;
            }
            setTimeout(tick, 35);
        }

        tick();
    }

    function initCounters() {
        var counters = document.querySelectorAll('[data-counter]');
        if (counters.length === 0) return;

        function finish(el) {
            el.textContent = el.getAttribute('data-counter');
        }

        if (reduceMotion || !('IntersectionObserver' in window)) {
            counters.forEach(finish);
            return;
        }

        function animate(el) {
            var target = parseInt(el.getAttribute('data-counter'), 10) || 0;
            var start = null;
            var duration = 900;

            function step(timestamp) {
                if (start === null) start = timestamp;
                var progress = Math.min((timestamp - start) / duration, 1);
                el.textContent = Math.floor(progress * target);
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target;
                }
            }

            requestAnimationFrame(step);
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animate(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        counters.forEach(function (el) { observer.observe(el); });
    }
})();
