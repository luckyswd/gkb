class Product {
    constructor() {
        this.navTabs = document.querySelectorAll('.product__body-nav-tab');
        this.contentElements = {
            0: document.querySelector('.product__body-content-benefits'),
            1: document.querySelector('.product__body-content-specifications'),
            2: document.querySelector('.product__body-content-equipment'),
            3: document.querySelector('.product__body-content-documentations')
        };
        this.init();
    }

    init() {
        this.navTabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                this.navTabs.forEach((otherTab) => {
                    otherTab.classList.remove('active');
                });
                Object.values(this.contentElements).forEach((contentElement) => {
                    contentElement && contentElement.classList.remove('active');
                });

                tab.classList.add('active');
                const contentElement = this.contentElements[index];
                contentElement && contentElement.classList.add('active');
            });
        });
    }
}

new Product();
