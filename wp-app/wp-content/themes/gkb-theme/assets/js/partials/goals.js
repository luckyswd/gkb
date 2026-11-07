/* Цели Яндекс.Метрики: телефон, email, «Где купить», скачивание PDF,
   отправка формы обратной связи. Сами цели (тип «JavaScript-событие»)
   заводятся в интерфейсе Метрики с теми же идентификаторами. */
(function () {
    const COUNTER = 94762651;

    function reach(goal) {
        if (typeof window.ym === 'function') {
            window.ym(COUNTER, 'reachGoal', goal);
        }
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link) {
            return;
        }
        const href = link.getAttribute('href') || '';
        if (href.startsWith('tel:')) {
            reach('phone_click');
        } else if (href.startsWith('mailto:')) {
            reach('email_click');
        } else if (/\.pdf(\?|#|$)/i.test(href)) {
            reach('pdf_download');
        } else if (/\/where-to-buy\//.test(href)) {
            reach('where_to_buy');
        }
    }, {passive: true});

    document.addEventListener('wpcf7mailsent', () => reach('form_sent'));
})();
