class Dealers {
    constructor() {
        this.regions = document.querySelectorAll('.dealers__region');

        this.init();
    }

    init() {
        this.handleAccordion()
    }

    handleAccordion() {
        this.regions && this.regions.forEach((region, index) => {
            region.addEventListener('click', () => {
                region.classList.toggle('active');
            });
        });
    }
}

new Dealers();
