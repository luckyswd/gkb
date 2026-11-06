window.ajaxUrl = window.ajaxUrl || '/wp-admin/admin-ajax.php';

/* Шапка: мобильное меню и переключатель языка.
   Раскрытие моделей категории сделано на CSS (:hover и :focus-within),
   скрипт для него не нужен. */
class Header {
    constructor() {
        this.burger = document.querySelector('.gkb-header__burger');
        this.menu = document.getElementById('gkb-mobile-menu');

        this.init();
    }

    init() {
        this.burgerToggle();
        this.langSwitch();
        this.closeOnEscape();
        this.closeOnDesktop();
    }

    isMenuOpen() {
        return !!this.menu && !this.menu.hasAttribute('hidden');
    }

    setMenu(open) {
        if (!this.burger || !this.menu) {
            return;
        }

        if (open) {
            this.menu.removeAttribute('hidden');
        } else {
            this.menu.setAttribute('hidden', '');
        }

        this.burger.setAttribute('aria-expanded', open ? 'true' : 'false');

        const label = open
            ? this.burger.dataset.labelClose
            : this.burger.dataset.labelOpen;

        if (label) {
            this.burger.textContent = label;
        }
    }

    burgerToggle() {
        this.burger && this.burger.addEventListener('click', () => {
            this.setMenu(!this.isMenuOpen());
        });
    }

    langSwitch() {
        document.addEventListener('click', async (event) => {
            const button = event.target.closest('.gkb-lang__btn');

            if (!button || button.classList.contains('is-active')) {
                return;
            }

            event.preventDefault();
            button.disabled = true;

            const formData = new FormData();
            formData.append('lang', button.dataset.lang);
            formData.append('action', 'select_lang');

            try {
                const response = await fetch(window.ajaxUrl, { method: 'POST', body: formData });
                const result = await response.json();

                if (result.status) {
                    location.reload();
                    return;
                }
            } catch (error) {
                /* переключение не удалось — возвращаем кнопку в рабочее состояние */
            }

            button.disabled = false;
        });
    }

    closeOnEscape() {
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (this.isMenuOpen()) {
                this.setMenu(false);
                this.burger && this.burger.focus();
                return;
            }

            /* снять фокус с раскрытой панели категории, чтобы она закрылась */
            const openPanel = document.activeElement && document.activeElement.closest('.gkb-header__cat');

            if (openPanel) {
                document.activeElement.blur();
            }
        });
    }

    closeOnDesktop() {
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 900 && this.isMenuOpen()) {
                this.setMenu(false);
            }
        });
    }
}

new Header();
