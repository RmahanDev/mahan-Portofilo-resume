/* Polish layer: loading screen, scroll reveal, page transitions, pointer light, animated skill bars.
   Works on top of common.js and is optional: if anything here fails the site simply shows its content. */
(function () {
    'use strict';

    var root = document.documentElement;
    if (!root.classList.contains('pf-js')) {
        return;
    }

    var reduceMotion = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    var finePointer = !!(window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches);
    var repeat = root.classList.contains('pf-repeat');
    var hasIO = 'IntersectionObserver' in window;

    var toFa = function (n) {
        return String(n).replace(/\d/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.charAt(d); });
    };

    /* ---------------------------------------------------------------
       Reveal targets: marked now (while the loading screen still covers the page),
       shown one by one as they scroll into view after it lifts.
    --------------------------------------------------------------- */
    var groups = [
        '.sidebar__base-info, .sidebar__additional-info',
        '.box-outer > :not(.nav-container):not(.row):not(.news-grid):not(.gallery-grid):not(.timeline):not(script)',
        '.box-outer > .row > *',
        '.news-grid > .news-item',
        '.gallery-grid__item > a',
        '.timeline > .timeline__item',
        '.box-inner .progress',
        '.header-post, .caption-post, .gallery-post, .footer-post, .related-posts',
        '.header-project, .details-info--inline, .swiper-container.js-carousel-project'
    ];

    var revealed = [];
    var seen = typeof Set === 'function' ? new Set() : null;

    if (!reduceMotion) {
        groups.forEach(function (selector) {
            var buckets = [];
            document.querySelectorAll(selector).forEach(function (el) {
                if (seen && seen.has(el)) { return; }
                if (el.closest('.mfp-wrap, .pswp, .swiper-wrapper')) { return; }
                if (seen) { seen.add(el); }
                var parent = el.parentNode;
                var bucket = buckets.filter(function (b) { return b.parent === parent; })[0];
                if (!bucket) {
                    bucket = { parent: parent, count: 0 };
                    buckets.push(bucket);
                }
                el.style.setProperty('--rd', Math.min(bucket.count, 7) * 70 + 'ms');
                bucket.count++;
                el.classList.add('reveal');
                revealed.push(el);
            });
        });
    }

    // A wrapper that already has revealed children (e.g. the whole article column) must not hide them
    // all behind one tall fade; cards (.box) are fine to animate together with their contents.
    revealed = revealed.filter(function (el) {
        var nested = el.querySelector('.reveal') !== null && !el.classList.contains('box');
        if (nested) {
            el.classList.remove('reveal');
            el.style.removeProperty('--rd');
        }
        return !nested;
    });

    /* Skill bars grow from zero and the percentage counts up. */
    var bars = [];
    if (!reduceMotion) {
        document.querySelectorAll('.progress').forEach(function (progress) {
            var fill = progress.querySelector('.progress-bar span');
            var text = progress.querySelector('.progress-text');
            if (!fill) { return; }
            var width = parseFloat(fill.style.width) || 0;
            var node = null;
            if (text) {
                for (var i = 0; i < text.childNodes.length; i++) {
                    if (text.childNodes[i].nodeType === 3 && /\d+\s*%/.test(text.childNodes[i].nodeValue)) {
                        node = text.childNodes[i];
                    }
                }
            }
            fill.style.width = '0%';
            bars.push({ el: progress, fill: fill, width: width, node: node, template: node ? node.nodeValue : '' });
        });
    }

    function playBar(bar) {
        bar.fill.style.width = bar.width + '%';
        if (!bar.node) { return; }
        var start = null;
        var tick = function (now) {
            if (start === null) { start = now; }
            var t = Math.min((now - start) / 1300, 1);
            var value = Math.round(bar.width * (1 - Math.pow(1 - t, 3)));
            bar.node.nodeValue = bar.template.replace(/\d+(\s*%)/, value + '$1');
            if (t < 1) { requestAnimationFrame(tick); }
        };
        requestAnimationFrame(tick);
    }

    function show(el) {
        el.classList.add('is-in');
        var done = function (e) {
            if (e.target !== el || e.propertyName !== 'opacity') { return; }
            el.removeEventListener('transitionend', done);
            el.classList.remove('reveal', 'is-in');
            el.style.removeProperty('--rd');
        };
        el.addEventListener('transitionend', done);
        bars.forEach(function (bar) { if (bar.el === el) { playBar(bar); } });
    }

    function startReveal() {
        var progressTargets = bars.filter(function (bar) { return revealed.indexOf(bar.el) === -1; });
        if (!hasIO) {
            revealed.forEach(show);
            progressTargets.forEach(playBar);
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    io.unobserve(entry.target);
                    show(entry.target);
                }
            });
        }, { threshold: 0, rootMargin: '0px 0px -6% 0px' });
        revealed.forEach(function (el) { io.observe(el); });
        progressTargets.forEach(function (bar) {
            var bio = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) { bio.disconnect(); playBar(bar); }
            }, { threshold: 0.4 });
            bio.observe(bar.el);
        });
    }

    /* ---------------------------------------------------------------
       Loading screen
    --------------------------------------------------------------- */
    var pre = document.getElementById('preloader');
    var started = (window.performance && performance.now) ? performance.now() : 0;
    var minTime = reduceMotion ? 150 : (repeat ? 380 : 1500);
    var loaded = document.readyState === 'complete';
    var progress = 0;
    var finished = false;

    window.addEventListener('load', function () { loaded = true; });

    function render(p) {
        if (!pre) { return; }
        pre.style.setProperty('--p', p.toFixed(3));
        var out = pre.querySelector('.preloader__percent');
        if (out) { out.textContent = toFa(Math.round(p * 100)) + '٪'; }
    }

    function finish() {
        if (finished) { return; }
        finished = true;
        try { sessionStorage.setItem('pf_seen', '1'); } catch (e) { /* private mode */ }
        root.classList.add('pf-ready');
        root.classList.remove('pf-loading');
        if (pre) {
            pre.classList.add('is-done');
            setTimeout(function () { if (pre.parentNode) { pre.parentNode.removeChild(pre); } }, 1100);
        }
        // Let the panels start moving, then bring the content in behind them.
        setTimeout(startReveal, reduceMotion ? 0 : 280);
    }

    function frame(now) {
        var elapsed = now - started;
        // Paced to the minimum display time so the ring, bar and logo finish together; waits near 90% for slow loads.
        var target = Math.min(loaded ? 1 : 0.9, elapsed / minTime);
        progress += (target - progress) * 0.25;
        if (progress > 0.995) { progress = 1; }
        render(progress);
        if (progress >= 1 && elapsed >= minTime) {
            finish();
        } else {
            requestAnimationFrame(frame);
        }
    }

    if (pre) {
        requestAnimationFrame(frame);
    } else {
        finish();
    }
    // Never leave a visitor stuck behind the loading screen.
    setTimeout(finish, 7000);

    /* ---------------------------------------------------------------
       Page transitions
    --------------------------------------------------------------- */
    var curtain = document.createElement('div');
    curtain.className = 'page-curtain';
    curtain.setAttribute('aria-hidden', 'true');
    document.body.appendChild(curtain);

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) { return; }
        var a = e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || (a.target && a.target !== '_self') || a.hasAttribute('download')) { return; }
        if (a.closest('.filter, .pswp, .mfp-wrap, [data-size], [data-zoom], .js-image, .popup')) { return; }
        var url;
        try { url = new URL(a.href, location.href); } catch (err) { return; }
        if (url.protocol.indexOf('http') !== 0 || url.origin !== location.origin) { return; }
        if (url.pathname === location.pathname && url.search === location.search) { return; }
        if (/\.(png|jpe?g|gif|webp|svg|pdf|zip|rar|ico)$/i.test(url.pathname)) { return; }
        e.preventDefault();
        root.classList.add('pf-leaving');
        setTimeout(function () { location.href = a.href; }, reduceMotion ? 0 : 240);
    });

    // Coming back with the browser's back button restores the page from cache.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) { root.classList.remove('pf-leaving'); }
    });

    /* ---------------------------------------------------------------
       Pointer light on cards
    --------------------------------------------------------------- */
    if (finePointer && !reduceMotion) {
        var spots = document.querySelectorAll('.case-item, .news-item, .box-inner, .review-item');
        spots.forEach(function (el) { el.classList.add('spot'); });
        var queued = null;
        document.addEventListener('pointermove', function (e) {
            var el = e.target.closest ? e.target.closest('.spot') : null;
            if (!el) { return; }
            var x = e.clientX, y = e.clientY;
            if (queued) { cancelAnimationFrame(queued); }
            queued = requestAnimationFrame(function () {
                var r = el.getBoundingClientRect();
                el.style.setProperty('--mx', (x - r.left) + 'px');
                el.style.setProperty('--my', (y - r.top) + 'px');
            });
        }, { passive: true });
    }

    /* Pages without a reading-progress line (everything but posts and projects) get one too. */
    if (!document.querySelector('.scroll-line')) {
        var line = document.createElement('div');
        line.className = 'scroll-line';
        document.body.insertBefore(line, document.body.firstChild);
    }
})();
