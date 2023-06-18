class Slider {
  constructor() {
    this.init()
  }

  init() {
    this.swiperInit()
  }

  swiperInit() {
    new Swiper(".gallery-swiper", {
      slidesPerView: 1,
      slidesPerGroup: 1,
      autoplay: {
        delay: 4000,
      },
    });
  }
}

new Slider();