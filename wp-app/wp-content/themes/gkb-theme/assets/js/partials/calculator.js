/* Калькулятор мощности котла: расчёт на сервере, здесь — без перезагрузки.
   Та же GET-страница запрашивается fetch, блок результата подменяется,
   адрес в строке браузера обновляется — ссылкой на расчёт можно поделиться.
   Агенту WebMCP (event.agentInvoked) результат возвращается текстом. */
(function () {
    const form = document.querySelector('.gkb-calc__form');
    if (!form) {
        return;
    }

    const result = document.querySelector('[data-calc-result]');

    function goal() {
        if (typeof window.ym === 'function') {
            window.ym(94762651, 'reachGoal', 'calc_result');
        }
    }

    async function run() {
        const params = new URLSearchParams(new FormData(form));
        const url = form.action.split('#')[0].split('?')[0] + '?' + params.toString();
        const response = await fetch(url, {credentials: 'same-origin'});
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const fresh = doc.querySelector('[data-calc-result]');
        const freshForm = doc.querySelector('.gkb-calc__form');
        if (fresh && result) {
            result.innerHTML = fresh.innerHTML;
        }
        if (freshForm) {
            form.querySelectorAll('.gkb-field-error').forEach((el) => el.remove());
            form.querySelectorAll('.gkb-field--error').forEach((el) => el.classList.remove('gkb-field--error'));
            freshForm.querySelectorAll('.gkb-field--error').forEach((field) => {
                const input = field.querySelector('input');
                const own = input && form.querySelector('[name="' + input.name + '"]');
                const box = own && own.closest('.gkb-calc__field');
                const error = field.querySelector('.gkb-field-error');
                if (box && error) {
                    box.classList.add('gkb-field--error');
                    box.appendChild(error.cloneNode(true));
                }
            });
        }
        history.replaceState(null, '', url + '#raschet');
        const kw = result && result.querySelector('[data-calc-kw]');
        if (kw) {
            goal();
        }
        return result ? result.innerText.trim() : '';
    }

    form.addEventListener('submit', (event) => {
        if (!window.fetch || !window.DOMParser) {
            return; // обычная отправка формы
        }
        event.preventDefault();
        const promise = run().catch(() => {
            form.submit();
            return '';
        });
        if (event.agentInvoked && typeof event.respondWith === 'function') {
            event.respondWith(promise);
        }
    });

    // результат пришёл в адресе (переход по ссылке на расчёт)
    if (result && result.querySelector('[data-calc-kw]')) {
        goal();
    }
})();
