class Header {
    constructor() {
        this.init()
    }

    init() {
        this.selectLang()
    }

    selectLang() {
        const selectLang = document.querySelector('.select-lang .select-lang__item')
        selectLang.addEventListener('click', (event) => {
            event.preventDefault();
            document.cookie = "lang=" + selectLang.getAttribute('data-lang');
            location.reload();
        })
    }
}

new Header();
window.ajaxUrl = '/wp-admin/admin-ajax.php';