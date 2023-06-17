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
        this.closeBurger()
    }

    selectLang() {
        const selectLang = document.querySelector('.select-lang .select-lang__item')
        selectLang.addEventListener('click', async (event) => {
            event.preventDefault();
            const formData = new FormData();
            formData.append('lang', selectLang.getAttribute('data-lang'))
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
                this.headerMobile && this.headerMobile.classList.add('active')
                this.body && this.body.classList.add('mobile')
        })
    }

    closeBurger() {
        this.headerMobile && this.headerMobile.addEventListener('click', () => {
            this.headerMobile && this.headerMobile.classList.remove('active')
            this.body && this.body.classList.remove('mobile')
        })
    }
}

new Header();
window.ajaxUrl = '/wp-admin/admin-ajax.php';