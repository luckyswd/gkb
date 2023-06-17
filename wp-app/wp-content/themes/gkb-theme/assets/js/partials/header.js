class Header {
    constructor() {
        this.init()
    }

    init() {
        this.selectLang()
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
}

new Header();
window.ajaxUrl = '/wp-admin/admin-ajax.php';