/* global nangThoSearch */
(function () {
    var input = document.getElementById('search-input-desktop');
    if (!input || typeof nangThoSearch === 'undefined') return;

    var debounceTimer = null;
    var dropdown = null;
    var activeIndex = -1;
    var wrapper = input.closest('.relative');

    // ── Dropdown lifecycle ────────────────────────────────────────────
    function ensureDropdown() {
        if (!dropdown || !wrapper.contains(dropdown)) {
            dropdown = document.createElement('div');
            dropdown.id = 'search-autocomplete-dropdown';
            dropdown.className = [
                'absolute top-full left-0 right-0 mt-1 z-50',
                'bg-white dark:bg-[#2c1621]',
                'rounded-xl border border-gray-100 dark:border-gray-700',
                'shadow-xl overflow-hidden',
            ].join(' ');
            wrapper.appendChild(dropdown);
        }
        return dropdown;
    }

    function hideDropdown() {
        if (dropdown && wrapper.contains(dropdown)) {
            wrapper.removeChild(dropdown);
        }
        dropdown = null;
        activeIndex = -1;
    }

    // ── HTML helpers ─────────────────────────────────────────────────
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function sectionTitle(label) {
        return '<div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">' + label + '</div>';
    }

    // ── Render ────────────────────────────────────────────────────────
    function render(data) {
        var terms = data.popular_terms || [];
        var cats  = data.categories   || [];
        var prods = data.products     || [];

        if (!terms.length && !cats.length && !prods.length) {
            hideDropdown();
            return;
        }

        var html = '';

        if (terms.length) {
            html += '<div class="p-3 border-b border-gray-100 dark:border-gray-800">';
            html += sectionTitle('Từ khoá phổ biến');
            html += '<div class="flex flex-wrap gap-2">';
            terms.forEach(function (term) {
                var url = nangThoSearch.shopUrl + '?s=' + encodeURIComponent(term);
                html += '<a href="' + esc(url) + '" class="search-ac-item flex items-center gap-1 px-3 py-1 rounded-full bg-pink-50 dark:bg-pink-900/20 text-primary text-sm hover:bg-pink-100 dark:hover:bg-pink-900/40 transition-colors">'
                    + '<span class="material-symbols-outlined text-[14px]">local_fire_department</span>'
                    + esc(term)
                    + '</a>';
            });
            html += '</div></div>';
        }

        if (cats.length) {
            html += '<div class="p-3 border-b border-gray-100 dark:border-gray-800">';
            html += sectionTitle('Danh mục');
            cats.forEach(function (cat) {
                html += '<a href="' + esc(cat.url) + '" class="search-ac-item flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">'
                    + '<span class="material-symbols-outlined text-[16px] text-gray-400">folder</span>'
                    + '<span>' + esc(cat.name) + '</span>'
                    + '<span class="ml-auto text-xs text-gray-400">(' + esc(cat.count) + ')</span>'
                    + '</a>';
            });
            html += '</div>';
        }

        if (prods.length) {
            html += '<div class="p-3">';
            html += sectionTitle('Sản phẩm gợi ý');
            html += '<div class="space-y-0.5">';
            prods.forEach(function (prod) {
                html += '<a href="' + esc(prod.url) + '" class="search-ac-item flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">'
                    + '<img src="' + esc(prod.image) + '" alt="" class="w-10 h-10 object-cover rounded-lg flex-shrink-0 bg-gray-100">'
                    + '<div class="flex-1 min-w-0">'
                    + '<div class="text-sm font-medium text-gray-800 dark:text-white truncate">' + esc(prod.name) + '</div>'
                    + '<div class="text-xs text-primary font-bold">' + esc(prod.price) + '</div>'
                    + '</div>'
                    + '</a>';
            });
            html += '</div></div>';
        }

        ensureDropdown().innerHTML = html;
    }

    // ── AJAX ──────────────────────────────────────────────────────────
    function fetchSuggestions(query) {
        var fd = new FormData();
        fd.append('action', 'nang_tho_live_search');
        fd.append('nonce',  nangThoSearch.nonce);
        fd.append('query',  query);

        fetch(nangThoSearch.ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) render(data.data);
                else hideDropdown();
            })
            .catch(hideDropdown);
    }

    // ── Keyboard navigation ───────────────────────────────────────────
    function getItems() {
        return dropdown ? Array.from(dropdown.querySelectorAll('.search-ac-item')) : [];
    }

    function setActive(index) {
        var items = getItems();
        items.forEach(function (el, i) {
            el.classList.toggle('bg-gray-50', i === index);
            el.classList.toggle('dark:bg-white/5', i === index);
        });
        activeIndex = index;
        if (index >= 0 && items[index]) items[index].focus();
    }

    // ── Events ────────────────────────────────────────────────────────
    input.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = this.value.trim();
        if (q.length < 2) { hideDropdown(); return; }
        debounceTimer = setTimeout(function () { fetchSuggestions(q); }, 300);
    });

    input.addEventListener('keydown', function (e) {
        var items = getItems();
        if (e.key === 'Escape') { hideDropdown(); input.blur(); return; }
        if (!items.length) return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(Math.min(activeIndex + 1, items.length - 1));
        }
        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeIndex <= 0) { activeIndex = -1; input.focus(); return; }
            setActive(activeIndex - 1);
        }
    });

    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) hideDropdown();
    });

    input.addEventListener('focus', function () {
        var q = this.value.trim();
        if (q.length >= 2) fetchSuggestions(q);
    });
})();
