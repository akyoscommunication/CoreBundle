import Swiper from 'swiper/dist/js/swiper.min';

class Slider {
    constructor() {}

    static initThumbs() {
        let galleryTop = new Swiper('.gallery-top', {
            spaceBetween: 10,
            onSlideChangeEnd: function() {
                $(document).trigger("slide-change");
            },
            loopedSlides: $(".gallery-top .swiper-wrapper .swiper-slide").length,
            loop: true
        });

        let galleryThumbs = new Swiper('.gallery-thumbs', {
            spaceBetween: 10,
            centeredSlides: false,
            loopedSlides: $(".gallery-thumbs .swiper-wrapper .swiper-slide").length,
            slidesPerView: 5,
            touchRatio: 0.2,
            slideToClickedSlide: true,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            loop: true
        });

        galleryTop.controller.control = galleryThumbs;
        galleryThumbs.controller.control = galleryTop;
    }

    static init() {
        new Swiper('.gallery', {
            spaceBetween: 50,
            centeredSlides: false,
            slidesPerView: 2,
            speed: 1000,
            autoplay: {
                delay: 2500,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            loop: true
        });
    }
}

export default Slider
