class Header {
    constructor() {
        this.headerMenu = document.querySelector('.header__menu');
        this.headerSearch = document.querySelector('.header__search');
        this.headerSearchClose = document.querySelector('.header__search-close');
        this.headerSearchOpen = document.querySelector('.header__search-icon');
        this.headerLogoMobile = document.querySelector('.header__logo-mobile');
        this.headerBurgerIcon = document.querySelector('.header__burger-icon');
        this.container = document.querySelector('.container');
        this.headerMain = document.querySelector('.header__main');
        this.headerMobile = document.querySelector('.header__mobile');
        this.body = document.querySelector('body');

        this.init()
    }

    init() {
        this.selectLang()
        this.openSearch()
        this.closeSearch()
        this.openBurger()
    }

    selectLang() {
        const selectLang = document.querySelector('.select-lang');
        const languages = document.querySelectorAll('.lang-wrapper .select-lang__item')

        selectLang && selectLang.addEventListener('click', () => {
            selectLang.classList.toggle('js-active')
        })

        languages.forEach((lang) => {
            lang.addEventListener('click', async (event) => {
                event.preventDefault();
                const formData = new FormData();
                formData.append('lang', lang.getAttribute('data-lang'))
                formData.append('action', 'select_lang')
                const response = await fetch(window.ajaxUrl,
                  {
                      method: 'POST',
                      body: formData
                  }
                );

                const resp = await response.json();

                if (resp.status) {
                    location.reload();
                }
            })
        })
    }

    openSearch() {
        this.headerSearchOpen && this.headerSearchOpen.addEventListener('click', () => {
            if (window.innerWidth > 1280) {
                this.headerMenu && this.headerMenu.classList.add('disable')
                this.headerSearchOpen && this.headerSearchOpen.classList.add('disable')
                this.headerSearch && this.headerSearch.classList.add('active')
            } else {
                this.headerLogoMobile && this.headerLogoMobile.classList.add('disable')
                this.headerSearchOpen && this.headerSearchOpen.classList.add('disable')
                this.headerBurgerIcon && this.headerBurgerIcon.classList.add('disable')
                this.headerSearch && this.headerSearch.classList.add('active')
                this.container && this.container.classList.add('mobile')
                this.headerMain && this.headerMain.classList.add('mobile')
            }
        })
    }

    closeSearch() {
        this.headerSearchClose && this.headerSearchClose.addEventListener('click', () => {
            if (window.innerWidth > 1280) {
                this.headerMenu && this.headerMenu.classList.remove('disable')
                this.headerSearchOpen && this.headerSearchOpen.classList.remove('disable')
                this.headerSearch && this.headerSearch.classList.remove('active')
            } else {
                this.headerLogoMobile && this.headerLogoMobile.classList.remove('disable')
                this.headerSearchOpen && this.headerSearchOpen.classList.remove('disable')
                this.headerBurgerIcon && this.headerBurgerIcon.classList.remove('disable')
                this.headerSearch && this.headerSearch.classList.remove('active')
                this.container && this.container.classList.remove('mobile')
                this.headerMain && this.headerMain.classList.remove('mobile')
            }

        })
    }

    openBurger() {
        this.headerBurgerIcon && this.headerBurgerIcon.addEventListener('click', () => {
            this.headerMobile && this.headerMobile.classList.toggle('active');
            this.body && this.body.classList.toggle('mobile');
        });
    }
}

new Header();
window.ajaxUrl = '/wp-admin/admin-ajax.php';