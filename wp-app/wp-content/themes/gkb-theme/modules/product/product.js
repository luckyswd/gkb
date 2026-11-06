/* Страница товара: вкладки и галерея.
   Миниатюры — обычные кнопки, а не второй Swiper: так их раскладкой
   (вертикально на десктопе, горизонтально на телефоне) управляет CSS. */
class Product {
    constructor() {
        this.initTabs();
        this.initGallery();
    }

    initTabs() {
        const tabs = document.querySelectorAll('.gkb-product__tabs [data-tab]');

        if (!tabs.length) {
            return;
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const id = tab.dataset.tab;

                tabs.forEach((other) => other.classList.toggle('is-active', other === tab));

                document.querySelectorAll('.gkb-product__pane').forEach((pane) => {
                    pane.classList.toggle('is-active', pane.dataset.pane === id);
                });
            });
        });
    }

    initGallery() {
        const main = document.querySelector('.gkb-gallery__main');

        if (!main || typeof Swiper === 'undefined') {
            return;
        }

        const thumbs = Array.from(document.querySelectorAll('.gkb-gallery__thumb'));
        const counter = document.querySelector('.gkb-gallery__counter');

        const swiper = new Swiper(main, {
            slidesPerView: 1,
            watchOverflow: true,
            pagination: counter ? { el: counter, type: 'fraction' } : false,
        });

        const sync = () => {
            thumbs.forEach((thumb, index) => {
                thumb.classList.toggle('is-active', index === swiper.activeIndex);
            });
        };

        swiper.on('slideChange', sync);

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener('click', () => swiper.slideTo(index));
        });

        sync();
    }
}

new Product();
