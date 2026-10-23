/* Admin panel behaviour: confirmations, image previews, slug helper and the small HTML editor. */
(function () {
    'use strict';

    // Confirm destructive buttons.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-confirm]');
        if (btn && !window.confirm(btn.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });


    // Skill bars: keep the "%" label next to each slider in sync.
    document.querySelectorAll('[data-percent-range]').forEach(function (range) {
        range.addEventListener('input', function () {
            range.nextElementSibling.textContent = range.value + '%';
        });
    });

    // Image fields: preview the chosen file or typed path.
    document.querySelectorAll('[data-image-field]').forEach(function (wrap) {
        var preview = wrap.querySelector('.admin-image__preview');
        var text = wrap.querySelector('input[type="text"]');
        var file = wrap.querySelector('input[type="file"]');
        var show = function (src) {
            preview.innerHTML = src ? '<img alt="">' : '<i class="feathericon-image"></i>';
            if (src) preview.querySelector('img').src = src;
        };
        file.addEventListener('change', function () {
            if (file.files[0]) {
                show(URL.createObjectURL(file.files[0]));
                file.parentNode.classList.add('has-file');
                file.parentNode.querySelector('span').textContent = file.files[0].name;
            }
        });
        text.addEventListener('change', function () {
            var v = text.value.trim();
            show(v && !/^(https?:)?\/\//.test(v) ? '../' + v : v);
        });
    });

    // Multiple file inputs: show how many files were chosen.
    document.querySelectorAll('[data-count-label]').forEach(function (input) {
        var out = input.closest('.form-group').querySelector('[data-count-output]');
        input.addEventListener('change', function () {
            out.textContent = input.files.length ? input.files.length + ' تصویر انتخاب شد؛ بعد از ذخیره اضافه می‌شوند.' : '';
        });
    });

    // Slug: suggest from a latin title when the slug is still empty.
    document.querySelectorAll('[data-slug-from]').forEach(function (slug) {
        var src = document.getElementById(slug.getAttribute('data-slug-from'));
        if (!src) return;
        var touched = slug.value !== '';
        slug.addEventListener('input', function () { touched = true; });
        src.addEventListener('input', function () {
            if (touched) return;
            slug.value = src.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
        });
    });

    // Minimal HTML editor: wraps the selection in tags, uploads images, toggles preview.
    function wrapSelection(ta, before, after, placeholder) {
        var start = ta.selectionStart, end = ta.selectionEnd;
        var selected = ta.value.slice(start, end) || placeholder || '';
        ta.setRangeText(before + selected + after, start, end, 'end');
        ta.focus();
        ta.dispatchEvent(new Event('input'));
    }

    function uploadImage(done) {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = function () {
            if (!input.files[0]) return;
            var data = new FormData();
            data.append('file', input.files[0]);
            data.append('_csrf', window.ADMIN_CSRF);
            fetch('upload.php', { method: 'POST', body: data, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.path) done(res.path);
                    else alert(res.error || 'آپلود ناموفق بود');
                })
                .catch(function () { alert('آپلود ناموفق بود'); });
        };
        input.click();
    }

    document.querySelectorAll('[data-editor]').forEach(function (editor) {
        var ta = editor.querySelector('textarea');
        var preview = editor.querySelector('.admin-editor__preview');
        var refresh = function () {
            // Preview is rendered from /admin, so site-relative paths need "../".
            preview.innerHTML = ta.value.replace(/(src|href)=(["'])(assets\/|uploads\/)/g, '$1=$2../$3');
        };
        ta.addEventListener('input', function () { if (!preview.hidden) refresh(); });

        editor.querySelector('.admin-editor__toolbar').addEventListener('click', function (e) {
            var btn = e.target.closest('button');
            if (!btn) return;
            switch (btn.getAttribute('data-cmd')) {
                case 'p': wrapSelection(ta, '<p>', '</p>', 'متن پاراگراف'); break;
                case 'h2': wrapSelection(ta, '<h2 class="title title--h2">', '</h2>', 'تیتر'); break;
                case 'h3': wrapSelection(ta, '<h3 class="title title--h3">', '</h3>', 'تیتر'); break;
                case 'strong': wrapSelection(ta, '<strong>', '</strong>', 'متن پررنگ'); break;
                case 'em': wrapSelection(ta, '<em>', '</em>', 'متن مورب'); break;
                case 'br': wrapSelection(ta, '<br>', '', ''); break;
                case 'ul': wrapSelection(ta, '<ul>\n    <li>', '</li>\n</ul>', 'آیتم'); break;
                case 'quote': wrapSelection(ta, '<blockquote class="block-quote">\n    <p>', '</p>\n    <span class="block-quote__author">نویسنده</span>\n</blockquote>', 'نقل قول'); break;
                case 'link':
                    var url = prompt('آدرس لینک:', 'https://');
                    if (url) wrapSelection(ta, '<a href="' + url.replace(/"/g, '&quot;') + '" target="_blank">', '</a>', 'متن لینک');
                    break;
                case 'image':
                    uploadImage(function (path) {
                        wrapSelection(ta, '<img class="gallery-post__item cover" src="' + path + '" data-zoom alt="">', '', '');
                    });
                    break;
                case 'preview':
                    preview.hidden = !preview.hidden;
                    ta.hidden = !preview.hidden;
                    btn.classList.toggle('is-active', !preview.hidden);
                    if (!preview.hidden) refresh();
                    break;
            }
        });
    });
})();
