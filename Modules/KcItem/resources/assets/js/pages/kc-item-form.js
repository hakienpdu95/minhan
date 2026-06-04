/**
 * pages/kc-item-form.js
 * Alpine component cho form tạo/sửa tài liệu KC.
 */

function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .replace(/[đĐ]/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

document.addEventListener('alpine:init', function () {
    Alpine.data('kcItemForm', function () {
        return {
            slug: '',
            _slugEdited: false,

            init: function () {
                var slugEl = document.querySelector('[name="slug"]');
                if (slugEl && slugEl.value) {
                    this._slugEdited = true;
                }
            },

            onTitleInput: function (val) {
                if (!this._slugEdited) {
                    this.slug = slugify(val);
                }
            },
        };
    });
});

// Init TomSelect cho form
document.addEventListener('DOMContentLoaded', function () {
    if (!window.TomSelect) return;

    ['ts-type', 'ts-category', 'ts-visibility'].forEach(function (id) {
        var el = document.getElementById(id);
        if (!el) return;
        new window.TomSelect(el, {
            dropdownParent: 'body',
            create: false,
            plugins: ['clear_button'],
        });
    });

    // Tag picker — TomSelect multi với tạo tag mới inline
    var tagEl = document.getElementById('ts-tags');
    if (!tagEl) return;

    var tagsApiUrl = tagEl.dataset.apiUrl || '';

    new window.TomSelect(tagEl, {
        dropdownParent: 'body',
        valueField:  'value',
        labelField:  'text',
        searchField: ['text'],
        plugins:     ['remove_button'],
        maxItems:    null,
        preload:     true,
        create: function (input, callback) {
            // Tạo tag mới inline qua API
            var csrf = document.querySelector('meta[name=csrf-token]')?.content ?? '';
            fetch('/dashboard/kc-tags', {
                method:  'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: input,
                    slug: input.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/[đĐ]/g, 'd').replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-').replace(/-+/g, '-'),
                }),
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.id) {
                    callback({ value: data.id, text: data.name });
                } else {
                    callback(null);
                }
            })
            .catch(function () { callback(null); });
        },
        load: function (query, callback) {
            var url = tagsApiUrl + (query ? '?q=' + encodeURIComponent(query) : '');
            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            })
            .then(function (res) { return res.json(); })
            .then(function (data) { callback(Array.isArray(data) ? data : []); })
            .catch(function () { callback([]); });
        },
        render: {
            option: function (data) {
                var color = data.color || '#6366f1';
                return '<div class="flex items-center gap-2">'
                    + '<span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:' + color + '"></span>'
                    + '<span>' + data.text + '</span>'
                    + '</div>';
            },
            item: function (data) {
                var color = data.color || '#6366f1';
                return '<div class="flex items-center gap-1.5">'
                    + '<span class="w-2 h-2 rounded-full shrink-0" style="background:' + color + '"></span>'
                    + '<span>' + data.text + '</span>'
                    + '</div>';
            },
        },
    });
});
