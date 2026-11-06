/* Поиск по каталогу во весь экран: популярные запросы и мгновенные
   подсказки по товарам. Список товаров шапка кладёт в #gkb-search-data,
   поэтому подсказки работают без запросов к серверу. Enter отправляет
   обычную форму на /catalog?search=. */
(function () {
    const overlay = document.getElementById('gkb-search');

    if (!overlay) {
        return;
    }

    const input = overlay.querySelector('.gkb-search__input');
    const clear = overlay.querySelector('[data-search-clear]');
    const results = overlay.querySelector('[data-search-results]');
    const form = overlay.querySelector('.gkb-search__form');
    const dataNode = document.getElementById('gkb-search-data');
    let items = [];
    let lastTrigger = null;

    try {
        items = JSON.parse(dataNode ? dataNode.textContent : '[]') || [];
    } catch (error) {
        items = [];
    }

    const norm = (value) => String(value || '').toLowerCase().replace(/ё/g, 'е');

    const escapeHtml = (value) => String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const highlight = (text, tokens) => {
        let html = escapeHtml(text);
        tokens.forEach((token) => {
            if (!token) {
                return;
            }
            const safe = escapeHtml(token).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            html = html.replace(new RegExp('(' + safe + ')', 'i'), '<mark>$1</mark>');
        });
        return html;
    };

    const render = () => {
        const query = input.value.trim();
        clear.hidden = query === '';

        if (query === '') {
            results.innerHTML = '';
            return;
        }

        const tokens = norm(query).split(/\s+/).filter(Boolean);
        const found = items.filter((item) => {
            const haystack = norm(item.name + ' ' + item.sub + ' ' + item.cat);
            return tokens.every((token) => haystack.indexOf(token) !== -1);
        }).slice(0, 8);

        const allUrl = form.getAttribute('action') + '?search=' + encodeURIComponent(query);

        if (!found.length) {
            results.innerHTML =
                '<p class="gkb-search__empty">' + escapeHtml(overlay.dataset.textEmpty) + '</p>' +
                '<a class="gkb-search__all" href="' + escapeHtml(allUrl) + '">' + escapeHtml(overlay.dataset.textAll) + ' →</a>';
            return;
        }

        const rawTokens = query.split(/\s+/).filter(Boolean);

        results.innerHTML = '<div class="gkb-search__list">' + found.map((item) => (
            '<a class="gkb-search__item" href="' + escapeHtml(item.url) + '">' +
                '<span class="gkb-search__pic">' +
                    (item.img ? '<img src="' + escapeHtml(item.img) + '" alt="" loading="lazy">' : '') +
                '</span>' +
                '<span class="gkb-search__text">' +
                    '<span class="gkb-search__cat">' + escapeHtml(item.cat) + '</span>' +
                    '<span class="gkb-search__name">' + highlight(item.name, rawTokens) + '</span>' +
                    (item.sub ? '<span class="gkb-search__sub">' + highlight(item.sub, rawTokens) + '</span>' : '') +
                '</span>' +
            '</a>'
        )).join('') + '</div>' +
            '<a class="gkb-search__all" href="' + escapeHtml(allUrl) + '">' + escapeHtml(overlay.dataset.textAll) + ' →</a>';
    };

    const open = (trigger) => {
        lastTrigger = trigger || null;

        // если открыто мобильное меню — закрываем его
        const burger = document.querySelector('.gkb-header__burger[aria-expanded="true"]');
        if (burger) {
            burger.click();
        }

        overlay.hidden = false;
        document.documentElement.classList.add('gkb-no-scroll');
        render();
        window.setTimeout(() => input.focus(), 30);
    };

    const close = () => {
        overlay.hidden = true;
        document.documentElement.classList.remove('gkb-no-scroll');
        if (lastTrigger) {
            lastTrigger.focus();
        }
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-search-open]');
        if (trigger) {
            event.preventDefault();
            open(trigger);
            return;
        }

        if (event.target.closest('[data-search-close]')) {
            close();
            return;
        }

        const chip = event.target.closest('[data-search-term]');
        if (chip && overlay.contains(chip)) {
            input.value = chip.dataset.searchTerm;
            render();
            input.focus();
        }
    });

    clear.addEventListener('click', () => {
        input.value = '';
        render();
        input.focus();
    });

    input.addEventListener('input', render);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !overlay.hidden) {
            close();
        }
    });
})();
