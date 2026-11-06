/* Подсказки к иконкам характеристик.
   С мышью подсказка показывается по наведению средствами CSS, а здесь —
   показ по нажатию: на сенсорном экране наведения нет. Иконка — это button,
   поэтому нажатие по ней не открывает карточку товара. */
(function () {
    const closeAll = (except) => {
        document.querySelectorAll('.gkb-ficon.is-open').forEach((el) => {
            if (el !== except) {
                el.classList.remove('is-open');
            }
        });
    };

    document.addEventListener('click', (event) => {
        const icon = event.target.closest('.gkb-ficon');

        closeAll(icon);

        if (!icon) {
            return;
        }

        event.preventDefault();
        icon.classList.toggle('is-open');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll(null);
        }
    });
})();
