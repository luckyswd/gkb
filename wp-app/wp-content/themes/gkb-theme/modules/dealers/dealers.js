class Dealers {
    constructor() {
        this.regions = document.querySelectorAll('.dealers__region-title');

        this.init();
    }

    init() {
        this.handleAccordion()
    }

    handleAccordion() {
        this.regions && this.regions.forEach((region, index) => {
            region.addEventListener('click', () => {
                const parent = region.parentElement;
                parent.classList.toggle('active');
            });
        });
    }
}

new Dealers();
