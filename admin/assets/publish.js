/* Publish control in the admin forms: the Apple-style wheel pickers for a scheduled time, and their live preview.
   Markup is rendered by render_schedule() in app/admin/fields.php. Hidden inputs carry the values that are saved. */
(function () {
    'use strict';

    // Height of one wheel item in px. Must match .admin-wheel__item and the list padding in admin/assets/admin.css.
    var ITEM = 36;
    var WHEELS = ['day', 'month', 'year', 'hour', 'minute'];

    function visibleItems(w) {
        return w.items.filter(function (item) { return !item.hidden; });
    }

    function scrollWheel(list, top, smooth) {
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (smooth && !reduce && typeof list.scrollTo === 'function') {
            list.scrollTo({ top: top, behavior: 'smooth' });
        } else {
            list.scrollTop = top;
        }
    }

    // Items far from the centre line fade and shrink, as on the iOS date picker.
    // Hidden items are always at the end of a wheel, so the index of an item is also its position.
    function paint(w) {
        var centre = w.list.scrollTop / ITEM;
        w.items.forEach(function (item, i) {
            if (item.hidden) { return; }
            var d = Math.abs(i - centre);
            item.style.opacity = String(Math.max(0.2, 1 - d * 0.3));
            item.style.transform = 'scale(' + Math.max(0.84, 1 - d * 0.06).toFixed(3) + ')';
        });
    }

    // Selects a visible item by its index among the visible items, and optionally scrolls it into the centre.
    function choose(w, index, scroll) {
        var shown = visibleItems(w);
        if (!shown.length) { return; }
        index = Math.max(0, Math.min(shown.length - 1, index));
        shown.forEach(function (item, i) {
            var on = i === index;
            item.classList.toggle('is-selected', on);
            item.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        w.index = index;
        w.input.value = shown[index].getAttribute('data-value');
        if (scroll) {
            scrollWheel(w.list, index * ITEM, true);
        }
        paint(w);
    }

    // Shows only the first $count days. Hiding the rest keeps each day at its own position.
    function setDayCount(w, count) {
        w.items.forEach(function (item, i) {
            item.hidden = i >= count;
            if (item.hidden) { item.classList.remove('is-selected'); }
        });
        if (w.index > count - 1) {
            choose(w, count - 1, true);
        } else {
            paint(w);
        }
    }

    // Days in the chosen month: 31 in the first six months, 30 in the next five, and 29 or 30 in Esfand
    // (the year item carries that number in data-esfand, computed on the server).
    function monthDays(state) {
        var month = parseInt(state.month.input.value, 10);
        if (month <= 6) { return 31; }
        if (month <= 11) { return 30; }
        var year = visibleItems(state.year)[state.year.index];
        return parseInt(year.getAttribute('data-esfand'), 10) || 29;
    }

    function selectedText(w) {
        var shown = visibleItems(w);
        return shown[w.index] ? shown[w.index].textContent : '';
    }

    function refresh(state) {
        setDayCount(state.day, monthDays(state));
        state.preview.textContent = selectedText(state.day) + ' ' + selectedText(state.month) + ' ' + selectedText(state.year)
            + ' ساعت ' + selectedText(state.hour) + ':' + selectedText(state.minute);
    }

    function makeWheel(el) {
        var list = el.querySelector('.admin-wheel__list');
        var w = {
            list: list,
            input: el.querySelector('input[type="hidden"]'),
            items: Array.prototype.slice.call(list.querySelectorAll('.admin-wheel__item')),
            index: 0,
            timer: 0,
            frame: 0
        };
        visibleItems(w).forEach(function (item, i) {
            if (item.classList.contains('is-selected')) { w.index = i; }
        });
        return w;
    }

    function wire(w, state) {
        w.list.addEventListener('scroll', function () {
            if (!w.frame) {
                w.frame = window.requestAnimationFrame(function () { w.frame = 0; paint(w); });
            }
            // When scrolling stops, the wheel snaps to the item in the centre and the value is saved.
            window.clearTimeout(w.timer);
            w.timer = window.setTimeout(function () {
                choose(w, Math.round(w.list.scrollTop / ITEM), false);
                refresh(state);
            }, 120);
        });

        w.list.addEventListener('click', function (e) {
            var item = e.target.closest ? e.target.closest('.admin-wheel__item') : null;
            if (!item || item.hidden) { return; }
            choose(w, visibleItems(w).indexOf(item), true);
            refresh(state);
        });

        w.list.addEventListener('keydown', function (e) {
            var steps = { ArrowUp: -1, ArrowDown: 1, PageUp: -5, PageDown: 5 };
            var target = null;
            if (Object.prototype.hasOwnProperty.call(steps, e.key)) {
                target = w.index + steps[e.key];
            } else if (e.key === 'Home') {
                target = 0;
            } else if (e.key === 'End') {
                target = visibleItems(w).length - 1;
            }
            if (target === null) { return; }
            e.preventDefault();
            choose(w, target, true);
            refresh(state);
        });
    }

    function setup(root) {
        var state = { preview: root.querySelector('[data-preview]') };
        root.querySelectorAll('[data-wheel]').forEach(function (el) {
            state[el.getAttribute('data-wheel')] = makeWheel(el);
        });
        var complete = state.preview && WHEELS.every(function (name) { return state[name]; });
        if (!complete) { return; }

        WHEELS.forEach(function (name) { wire(state[name], state); });

        // The wheels sit in a hidden panel until "scheduled" is chosen. A scroll position can only be set once they are shown.
        function place() {
            WHEELS.forEach(function (name) {
                scrollWheel(state[name].list, state[name].index * ITEM, false);
                paint(state[name]);
            });
            refresh(state);
        }
        root.addEventListener('change', function (e) {
            var radio = e.target;
            if (radio.classList && radio.classList.contains('admin-publish__input') && radio.value === 'scheduled') {
                place();
            }
        });

        place();
    }

    document.querySelectorAll('[data-publish]').forEach(setup);
})();
