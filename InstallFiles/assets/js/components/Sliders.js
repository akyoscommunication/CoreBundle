import Swiper from 'swiper/js/swiper.min';

class Sliders {

    constructor() {
    }

    static init() {
        if ($('.component-slider').length) {
            new Swiper('.component-slider .swiper-container', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: true,
                autoplay: {
                    delay: 5000
                },
                navigation: {
                    prevEl: '.component-slider-prev',
                    nextEl: '.component-slider-next',
                },
            });
        }
    }
}

export default Sliders